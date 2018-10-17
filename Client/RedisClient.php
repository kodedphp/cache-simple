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

use Koded\Caching\Cache;
use Koded\Stdlib\Interfaces\Serializer;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\guard_cache_key;

/**
 * Class RedisClient uses the Redis PHP extension.
 *
 * @property \Redis client
 */
class RedisClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(\Redis $client, Serializer $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function get($key, $default = null)
    {
        guard_cache_key($key);

        return (bool)$this->client->exists($key)
            ? $this->serializer->unserialize($this->client->get($key))
            : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        guard_cache_key($key);

        if (null === $ttl) {
            return $this->client->set($key, $this->serializer->serialize($value));
        }

        if ($ttl > 0) {
            return $this->client->setex($key, $ttl, $this->serializer->serialize($value));
        }

        // The item is considered expired and must be deleted
        $this->client->del($key);

        return false === (bool)$this->client->exists($key);
    }

    public function delete($key)
    {
        guard_cache_key($key);

        return 1 === $this->client->del($key);
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
        guard_cache_key($key);

        return (bool)$this->client->exists($key);
    }
}
