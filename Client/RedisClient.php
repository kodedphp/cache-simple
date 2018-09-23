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

use Exception;
use Koded\Caching\CacheException;
use Koded\Caching\Configuration\RedisConfiguration;
use Psr\SimpleCache\CacheInterface;
use Redis;
use RedisException;

/**
 * Class RedisClient uses the Redis PHP extension.
 *
 * @property Redis client
 */
class RedisClient implements CacheInterface
{

    use ClientTrait, MultiplesTrait;

    public function __construct(Redis $client, RedisConfiguration $config)
    {
        $this->client = $client;

        try {
            // Because connect() does not throw exception, but E_WARNING
            if (false === @$this->client->connect(...$config->getConnectionParams())) {
                // @codeCoverageIgnoreStart
                throw CacheException::withConnectionErrorFor('Redis');
                // @codeCoverageIgnoreEnd
            }

            $this->client->setOption(Redis::OPT_SERIALIZER, $config->getSerializerType());
            $this->client->setOption(Redis::OPT_PREFIX, $config->get('prefix'));
            $this->client->select((int)$config->get('db', 0));

            if ($auth = $config->get('auth')) {
                $this->client->auth($auth);
            }

        } catch (RedisException $e) {
            error_log($e->getMessage());
            throw CacheException::withConnectionErrorFor('Redis');
        } catch (CacheException $e) {
            throw $e;
        } catch (Exception $e) {
            throw CacheException::generic($e->getMessage(), $e);
        }
    }

    public function get($key, $default = null)
    {
        return $this->client->exists($key) ? $this->client->get($key) : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        if (null === $ttl) {
            return $this->client->set($key, $value);
        }

        if ($ttl > 0) {
            return $this->client->setex($key, $ttl, $value);
        }

        // The item is considered expired and must be deleted
        $this->client->del($key);

        return false === $this->has($key);
    }

    public function delete($key)
    {
        return $this->client->del($key) > 0;
    }

    public function deleteMultiple($keys)
    {
        return $this->client->del($keys) === count($keys);
    }

    public function clear()
    {
        return $this->client->flushAll();
    }

    public function has($key)
    {
        return (bool)$this->client->exists($key);
    }
}
