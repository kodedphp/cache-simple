<?php
/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 */

namespace Koded\Caching\Client;

use Koded\Caching\Cache;
use Koded\Stdlib\Serializer;
use function Koded\Caching\verify_key;
use function Koded\Stdlib\json_serialize;

/**
 * RedisJsonClient uses the Redis PHP extension.
 *
 * It will create 2 entries in Redis
 * - one as JSON cache item
 * - and other as serialized PHP value
 *
 * The first is useful for other programming languages to use it,
 * and the PHP serialized variant is useful only for PHP applications
 * where the cached item is handled by PHP serialization.
 *
 */
final class RedisJsonClient implements Cache
{
    use ClientTrait, MultiplesTrait;

    private string $suffix;
    private int $options;
    private Serializer $serializer;

    public function __construct(\Redis $client, Serializer $serializer, int $options, int $ttl = null)
    {
        $this->suffix = '__' . $serializer->type() . '__';
        $this->serializer = $serializer;
        $this->options = $options;
        $this->client = $client;
        $this->ttl = $ttl;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key)
            ? $this->serializer->unserialize($this->client->get($key . $this->suffix))
            : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        verify_key($key);
        $expiration = $this->secondsWithGlobalTtl($ttl);
        if (null === $ttl && 0 === $expiration) {
            return $this->client->set($key, json_serialize($value, $this->options))
                && $this->client->set($key . $this->suffix, $this->serializer->serialize($value));
        }
        if ($expiration > 0) {
            return $this->client->setex($key, $expiration, json_serialize($value, $this->options))
                && $this->client->setex($key . $this->suffix, $expiration, $this->serializer->serialize($value));
        }
        $this->client->del([$key, $key . $this->suffix]);
        return true;
    }

    public function delete(string $key): bool
    {
        if (false === $this->has($key)) {
            return true;
        }
        return 2 === $this->client->del([$key, $key . $this->suffix]);
    }

    public function clear(): bool
    {
        return $this->client->flushDB();
    }

    public function has(string $key): bool
    {
        verify_key($key);
        return (bool)$this->client->exists($key)
            && (bool)$this->client->exists($key . $this->suffix);
    }
}
