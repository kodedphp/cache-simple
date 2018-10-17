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
use function Koded\Caching\guard_cache_key;

/**
 * Class PredisClient uses the Predis library.
 *
 * @property Client client
 */
final class PredisClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(Client $client, /*PredisConfiguration $config,*/ Serializer $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function get($key, $default = null)
    {
        guard_cache_key($key);

        return $this->client->exists($key) > 0
            ? $this->serializer->unserialize($this->client->get($key))
            : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        guard_cache_key($key);

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

    public function delete($key)
    {
        guard_cache_key($key);

        return $this->client->del($key) > 0;
    }

    public function deleteMultiple($keys)
    {
        return $this->client->del($keys) === count($keys);
    }

    public function has($key)
    {
        guard_cache_key($key);

//        return (bool)$this->client->exists($key);
        return $this->client->exists($key) > 0;
    }
}
