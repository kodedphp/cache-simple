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
use function Koded\Caching\verify_key;

/**
 * Class RedisClient uses the Redis PHP extension.
 *
 */
final class RedisClient implements CacheInterface, Cache
{
    use ClientTrait, MultiplesTrait;

    private $serializer;

    public function __construct(\Redis $client, Serializer $serializer, int $ttl = null)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->ttl = $ttl;
    }


    public function get($key, $default = null)
    {
        return $this->has($key)
            ? $this->serializer->unserialize($this->client->get($key))
            : $default;
    }


    public function set($key, $value, $ttl = null)
    {
        verify_key($key);
        $expiration = $this->secondsWithGlobalTtl($ttl);

        if (null === $ttl && 0 === $expiration) {
            return $this->client->set($key, $this->serializer->serialize($value));
        }

        if ($expiration > 0) {
            return $this->client->setex($key, $expiration, $this->serializer->serialize($value));
        }

        $this->client->del($key);

        return true;
    }


    public function delete($key)
    {
        if (false === $this->has($key)) {
            return true;
        }

        return 1 === $this->client->del($key);
    }


    public function clear()
    {
        return $this->client->flushDB();
    }


    public function has($key)
    {
        verify_key($key);

        return (bool)$this->client->exists($key);
    }
}
