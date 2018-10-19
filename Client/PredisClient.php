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

use Koded\Stdlib\Interfaces\Serializer;
use Predis\Client;
use function Koded\Caching\{cache_key_check, cache_ttl};

/**
 * Class PredisClient uses the Predis library.
 *
 * @property Client client
 */
final class PredisClient extends RedisClient
{

    public function __construct(Client $client, Serializer $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function set($key, $value, $ttl = null)
    {
        cache_key_check($key);
        $ttl = cache_ttl($ttl ?? $this->ttl);

        if (null === $ttl) {
            return 'OK' === $this->client->set($key, $this->serializer->serialize($value))->getPayload();
        }

        if ($ttl > 0) {
            return 'OK' === $this->client->setex($key, $ttl, $this->serializer->serialize($value))->getPayload();
        }

        // The item is considered expired and must be deleted
        $this->client->del($key);

        return !$this->has($key);
    }

    public function clear()
    {
        return 'OK' === $this->client->flushall()->getPayload();
    }
}
