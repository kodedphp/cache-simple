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
use Koded\Caching\{ Cache, CacheException };
use Koded\Caching\Configuration\RedisConfiguration;
use Psr\SimpleCache\CacheInterface;
use Redis;

/**
 * Class RedisClient uses the Redis PHP extension.
 */
class RedisClient implements CacheInterface
{

    use ClientTrait, RedisTrait;

    /**
     * @var Redis instance
     */
    protected $client;

    public function __construct(Redis $client, RedisConfiguration $config)
    {
        $this->client = $client;
        $this->keyRegex = $config->get('keyRegex', $this->keyRegex);

        try {
            // Because connect() does not throw exception, but E_WARNING
            if (false === @$this->client->connect(...$config->getConnectionParams())) {
                throw new CacheException(Cache::E_CONNECTION_ERROR, [':client' => 'Redis']);
            }

            $this->client->setOption(Redis::OPT_SERIALIZER, $config->getSerializerType());
            $this->client->setOption(Redis::OPT_PREFIX, $config->get('prefix'));

            if ($auth = $config->get('auth')) {
                $this->client->auth($auth);
            }

        } catch (CacheException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CacheException(Cache::E_PHP_EXCEPTION, [':message' => $e->getMessage()], $e);
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

        return !$this->has($key);
    }
}
