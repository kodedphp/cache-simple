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
class RedisJsonClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    protected $suffix;
    protected $options;

    /**
     * @var Serializer PHP by default. If available: msgpack, igbinary
     */
    protected $binarySerializer;

    public function __construct($client, Serializer $binarySerializer, int $options, ?int $ttl)
    {
        $this->client = $client;
        $this->options = $options;
        $this->suffix = '__' . $binarySerializer->type() . '__';
        $this->binarySerializer = $binarySerializer;
        $this->setTtl($ttl);
    }

    public function get($key, $default = null)
    {
        cache_key_check($key);

        if ($this->has($key)) {
            return $this->binarySerializer->unserialize($this->client->get($key . $this->suffix));
        }

        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        cache_key_check($key);

        if (null === $ttl) {
            return $this->client->set($key, json_serialize($value, $this->options))
                && $this->client->set($key . $this->suffix, $this->binarySerializer->serialize($value));
        }

        $ttl = cache_ttl($ttl ?? $this->ttl);

        if ($ttl > 0) {
            return $this->client->setex($key, $ttl, json_serialize($value, $this->options))
                && $this->client->setex($key . $this->suffix, $ttl, $this->binarySerializer->serialize($value));
        }

        // The item is considered expired and must be deleted
        $this->client->del($key, $key . $this->suffix);

        return false === $this->has($key);
    }

    public function delete($key)
    {
        cache_key_check($key);
        return 2 === $this->client->del($key, $key . $this->suffix);
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            cache_key_check($key);
            $keys[] = $key . $this->suffix;
        }

        return $this->client->del($keys) === count($keys);
    }

    public function clear()
    {
        return $this->client->flushAll();
    }

    public function has($key)
    {
        cache_key_check($key);

        return (bool)$this->client->exists($key)
            && (bool)$this->client->exists($key . $this->suffix);
    }
}
