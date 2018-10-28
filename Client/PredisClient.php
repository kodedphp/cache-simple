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
use Predis\Client;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\verify_key;

/**
 * Class PredisClient uses the Predis library.
 *
 * @property Client client
 */
final class PredisClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    private $serializer;

    public function __construct(Client $client, Serializer $serializer, int $ttl = null)
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

        if (null === $ttl) {
            return 'OK' === $this->client->set($key, $this->serializer->serialize($value))->getPayload();
        }

        $expiration = $this->secondsWithGlobalTtl($ttl);

        if ($expiration > 0) {
            return 'OK' === $this->client->setex($key, $expiration, $this->serializer->serialize($value))->getPayload();
        }

        // The item is considered expired and must be deleted
        return 1 === $this->client->del($key);
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
        return 'OK' === $this->client->flushdb()->getPayload();
    }

    public function has($key)
    {
        verify_key($key);

        return 1 === $this->client->exists($key);
    }
}
