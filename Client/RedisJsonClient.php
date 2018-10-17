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

/**
 * Class RedisJsonClient uses the Redis PHP extension to save the cache item as JSON.
 *
 * It will create 2 entries in Redis
 * - one as JSON cache item
 * - and other as serialized PHP value.
 *
 * The first is useful for other programming languages to use it,
 * and the PHP serialized variant is useful only for PHP applications
 * where the cached item is handled by PHP serialization.
 *
 * @property \Redis client
 */
final class RedisJsonClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    const SERIALIZED = '__serialized__';

    /**
     * @var Serializer
     */
    private $jsonSerializer;

    /**
     * @var Serializer PHP by default. If available: msgpack, igbinary
     */
    private $phpSerializer;

    public function __construct(\Redis $client, Serializer $jsonSerializer, Serializer $phpSerializer)
    {
        $this->client = $client;
        $this->jsonSerializer = $jsonSerializer;
        $this->phpSerializer = $phpSerializer;
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->phpSerializer->unserialize($this->client->get($key . self::SERIALIZED));
        }

        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        if (null === $ttl) {
            return $this->client->set($key, $this->jsonSerializer->serialize($value))
                && $this->client->set($key . self::SERIALIZED, $this->phpSerializer->serialize($value));
        }

        if ($ttl > 0) {
            return $this->client->setex($key, $ttl, $this->jsonSerializer->serialize($value))
                && $this->client->setex($key . self::SERIALIZED, $ttl, $this->phpSerializer->serialize($value));
        }

        // The item is considered expired and must be deleted
        $this->client->del($key, $key . self::SERIALIZED);

        return false === $this->has($key);
    }

    public function delete($key)
    {
        return 2 === $this->client->del($key, $key . self::SERIALIZED);
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $keys[] = $key . self::SERIALIZED;
        }

        return $this->client->del($keys) === count($keys);
    }

    public function clear()
    {
        return $this->client->flushAll();
    }

    public function has($key)
    {
        return (bool)$this->client->exists($key)
            && (bool)$this->client->exists($key . self::SERIALIZED);
    }
}
