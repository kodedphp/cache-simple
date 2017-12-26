<?php

/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 *
 */

namespace Koded\Caching\Client;

use Closure;
use Koded\Caching\{ Cache, CacheException, Configuration\RedisConfiguration };
use Psr\SimpleCache\CacheInterface;
use Redis;
use Throwable;

/**
 * Class RedisClient uses the Redis PHP extension.
 *
 * @method bool serialize($key, $value, $tty = null)
 * @method string unserialize($key, $default = null)
 */
class RedisClient implements CacheInterface
{

    use KeyTrait, ClientTrait;

    /**
     * @var Redis instance
     */
    protected $client;

    /**
     * @var Closure Data serializer
     */
    protected $serialize;

    /**
     * @var Closure Data normalizer
     */
    protected $unserialize;

    public function __construct(RedisConfiguration $config)
    {
        try {
            $this->client = new Redis;
            $this->keyRegex = $config->get('keyRegex', $this->keyRegex);

            if ($this->client->connect(...$config->getConnectionParams())) {
                $this->setSerializers(...$config->getSerializerParams());
                $this->client->setOption(Redis::OPT_PREFIX, $config->get('prefix'));

                if ($auth = $config->get('auth')) {
                    $this->client->auth($auth);
                }
            }
        } catch (Throwable $e) {
            throw new CacheException(Cache::E_PHP_EXCEPTION, [
                ':message' => $e->getMessage(),
                ':stacktrace' => $e->getTraceAsString()
            ]);
        }
    }

    public function get($key, $default = null)
    {
        // Cannot avoid exists() in this client, because FALSE is a valid value
        return $this->client->exists($key) ? ($this->unserialize)($key, $default) : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        return ($this->serialize)($key, $value, $ttl);
    }

    public function delete($key)
    {
        return $this->client->del($key) > 0;
    }

    public function clear()
    {
        return $this->client->flushAll();
    }

    public function getMultiple($keys, $default = null)
    {
        $cached = [];
        foreach ($keys as $key) {
            $cached[$key] = $this->get($key, $default);
        }

        return $cached;
    }

    public function setMultiple($values, $ttl = null)
    {
        $cached = 0;
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl) && ++$cached;
        }

        return count($values) === $cached;
    }

    public function deleteMultiple($keys)
    {
        return $this->client->del($keys);
    }

    public function has($key)
    {
        return (bool)$this->client->exists($key);
    }

    protected function setSerializers(int $serializer, Closure $setter, Closure $getter): void
    {
        $this->client->setOption(Redis::OPT_SERIALIZER, $serializer);

        $this->serialize = function(string $key, $value, $ttl = null) use ($setter): bool {
            if ($ttl < 0 || $ttl === 0) {
                // The item is considered expired and must be deleted
                $this->delete($key);

                return true;
            }

            if (null === $ttl) {
                return $this->client->set($key, $setter($value));
            }

            return $this->client->setex($key, $ttl, $setter($value));
        };

        $this->unserialize = function(string $key) use ($getter) {
            return $getter($this->client->get($key));
        };
    }
}