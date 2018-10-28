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
use Koded\Caching\Configuration\MemcachedConfiguration;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\verify_key;

/**
 * @property \Memcached client
 */
final class MemcachedClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    public function __construct(\Memcached $client, MemcachedConfiguration $config)
    {
        $this->client = $client;
        $this->ttl = $config->get('ttl');

        if (empty($this->client->getServerList())) {
            $this->client->addServers($config->getServers());
        }

        $this->client->setOptions($config->getOptions());
    }

    public function get($key, $default = null)
    {
        verify_key($key);

        // Cannot return get() directly because FALSE is a valid value
        $value = $this->client->get($key);

        return $this->client->getResultCode() === \Memcached::RES_NOTFOUND ? $default : $value;
    }

    public function set($key, $value, $ttl = null)
    {
        verify_key($key);
        $expiration = $this->secondsWithGlobalTtl($ttl);

        if ($ttl !== null && $expiration < 1) {
            return $this->client->delete($key);
        }

        return $this->client->set($key, $value, $expiration);
    }

    public function delete($key)
    {
        if (false === $this->has($key)) {
            return true;
        }

        return $this->client->delete($key);
    }

    public function clear()
    {
        return $this->client->flush();
    }

    public function has($key)
    {
        verify_key($key);

        // Memcached does not have exists() or similar method
        $this->client->get($key);

        return $this->client->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    /*
     *
     * Overrides
     *
     */

    private function internalMultiGet(array $keys, $default = null): iterable
    {
        return array_replace(array_fill_keys($keys, $default), $this->client->getMulti($keys) ?: []);
    }

    private function internalMultiSet(array $values, $ttl = null): bool
    {
        return $this->client->setMulti($values, $ttl);
    }

    private function internalMultiDelete(array $keys): bool
    {
        $this->client->deleteMulti($keys);

        return \Memcached::RES_FAILURE !== $this->client->getResultCode();
    }
}
