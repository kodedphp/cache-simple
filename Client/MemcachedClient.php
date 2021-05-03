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
use function Koded\Caching\verify_key;

/**
 * @property \Memcached client
 */
final class MemcachedClient implements Cache
{
    use ClientTrait, MultiplesTrait;

    public function __construct(\Memcached $client, int $ttl = null)
    {
        $this->ttl = $ttl;
        $this->client = $client;
    }

    public function get($key, $default = null)
    {
        verify_key($key);
        // Cannot return get() directly because default value
        $value = $this->client->get($key);
        return \Memcached::RES_SUCCESS === $this->client->getResultCode() ? $value : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        verify_key($key);
        $expiration = $this->secondsWithGlobalTtl($ttl);
        if (null !== $ttl && $expiration < 1) {
            $this->client->delete($key);
            return true;
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
        return \Memcached::RES_NOTFOUND !== $this->client->getResultCode();
    }

    /*
     *
     * Overrides
     *
     */

    protected function internalMultiGet(array $keys, $default = null): array
    {
        return array_replace(array_fill_keys($keys, $default), $this->client->getMulti($keys) ?: []);
    }

    protected function internalMultiSet(array $values, $ttl = null): bool
    {
        return $this->client->setMulti($values, $ttl);
    }

    protected function internalMultiDelete(array $keys): bool
    {
        $this->client->deleteMulti($keys);
        return \Memcached::RES_FAILURE !== $this->client->getResultCode();
    }
}
