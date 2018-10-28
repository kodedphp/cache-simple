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
use function Koded\Caching\{normalize_ttl, verify_key};
use function Koded\Stdlib\json_serialize;

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
 */
final class RedisJsonClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    private $suffix;
    private $options;

    /**
     * @var Serializer PHP by default. If available: msgpack, igbinary
     */
    protected $serializer;

    public function __construct(\Redis $client, Serializer $serializer, int $options, int $ttl = null)
    {
        $this->ttl = $ttl;
        $this->client = $client;
        $this->options = $options;
        $this->serializer = $serializer;
        $this->suffix = '__' . $serializer->type() . '__';
    }

    public function get($key, $default = null)
    {
        return $this->has($key)
            ? $this->serializer->unserialize($this->client->get($key . $this->suffix))
            : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        verify_key($key);
        $ttl = normalize_ttl($ttl ?? $this->ttl);

        if (null === $ttl) {
            return $this->client->set($key, json_serialize($value, $this->options))
                && $this->client->set($key . $this->suffix, $this->serializer->serialize($value));
        }

        if ($ttl > 0) {
            return $this->client->setex($key, $ttl, json_serialize($value, $this->options))
                && $this->client->setex($key . $this->suffix, $ttl, $this->serializer->serialize($value));
        }

        // The item is considered expired and must be deleted
        return 2 === $this->client->del([$key, $key . $this->suffix]);
    }

    public function delete($key)
    {
        if (false === $this->has($key)) {
            return true;
        }

        return 2 === $this->client->del([$key, $key . $this->suffix]);
    }

    public function clear()
    {
        return $this->client->flushDB();
    }

    public function has($key)
    {
        verify_key($key);

        return (bool)$this->client->exists($key)
            && (bool)$this->client->exists($key . $this->suffix);
    }
}
