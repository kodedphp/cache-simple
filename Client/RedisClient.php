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
use function Koded\Caching\{cache_key_check, cache_ttl};

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
    protected $serializer;

    public function __construct(\Redis $client, Serializer $serializer, ?int $ttl)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->setTtl($ttl);
    }

    public function get($key, $default = null)
    {
        cache_key_check($key);

        return (bool)$this->client->exists($key)
            ? $this->serializer->unserialize($this->client->get($key))
            : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        cache_key_check($key);

        if (null === $ttl) {
            return $this->client->set($key, $this->serializer->serialize($value));
        }

        $ttl = cache_ttl($ttl ?? $this->ttl);

        if ($ttl > 0) {
            return $this->client->setex($key, $ttl, $this->serializer->serialize($value));
        }

        // The item is considered expired and must be deleted
        $this->client->del($key);

        return false === (bool)$this->client->exists($key);
    }

    public function delete($key)
    {
        cache_key_check($key);

        if (false === (bool)$this->client->exists($key)) {
            return true;
        }

        return 1 === $this->client->del($key);
    }

    public function clear()
    {
        $this->client->flushAll();

        return null === $this->client->getLastError();
    }

    public function has($key)
    {
        cache_key_check($key);

        return (bool)$this->client->exists($key);
    }

    /*
     *
     * Overrides
     *
     */

    protected function multiDelete(array $keys): bool
    {
        $this->client->del($keys);

        return null === $this->client->getLastError();
    }
}
