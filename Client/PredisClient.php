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

/**
 * Class PredisClient uses the Predis library.
 *
 * @property \Predis\Client client
 */
final class PredisClient implements Cache
{
    use ClientTrait, MultiplesTrait;

    private Serializer $serializer;

    public function __construct(\Predis\Client $client, Serializer $serializer, int $ttl = null)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->ttl = $ttl;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key)
            ? $this->serializer->unserialize($this->client->get($key))
            : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        verify_key($key);
        $expiration = $this->secondsWithGlobalTtl($ttl);
        if (null === $ttl && 0 === $expiration) {
            return 'OK' === $this->client->set($key, $this->serializer->serialize($value))->getPayload();
        }
        if ($expiration > 0) {
            return 'OK' === $this->client->setex($key, $expiration, $this->serializer->serialize($value))->getPayload();
        }
        $this->client->del($key);
        return true;
    }


    public function delete(string $key): bool
    {
        if (false === $this->has($key)) {
            return true;
        }
        return 1 === $this->client->del($key);
    }

    public function clear(): bool
    {
        return 'OK' === $this->client->flushdb()->getPayload();
    }

    public function has(string $key): bool
    {
        verify_key($key);
        return 1 === $this->client->exists($key);
    }
}
