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
 * Class PredisJsonClient
 *
 */
final class PredisJsonClient implements CacheInterface, Cache
{
    use ClientTrait, MultiplesTrait;

    private $suffix;
    private $options;
    private $serializer;

    public function __construct(\Predis\Client $client, Serializer $serializer, int $options, int $ttl = null)
    {
        $this->suffix = '__' . $serializer->type() . '__';
        $this->serializer = $serializer;
        $this->options = $options;
        $this->client = $client;
        $this->ttl = $ttl;
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
            return 'OK' === $this->client->set($key, json_serialize($value, $this->options))->getPayload()
                && 'OK' === $this->client->set($key . $this->suffix, $this->serializer->serialize($value))
                    ->getPayload();
        }

        if ($ttl > 0) {
            return 'OK' === $this->client->setex($key, $ttl, json_serialize($value, $this->options))->getPayload()
                && 'OK' === $this->client->setex($key . $this->suffix, $ttl, $this->serializer->serialize($value))
                    ->getPayload();
        }

        // The item is considered expired and must be deleted
//        return $this->delete($key);
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
        return 'OK' === $this->client->flushDb()->getPayload();
    }

    public function has($key)
    {
        verify_key($key);

        return (bool)$this->client->exists($key)
            && (bool)$this->client->exists($key . $this->suffix);
    }
}
