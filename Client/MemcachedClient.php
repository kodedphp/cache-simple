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
use function Koded\Caching\{cache_key_check, cache_ttl};

/**
 * @property \Memcached client
 */
final class MemcachedClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    public function __construct(\Memcached $client, MemcachedConfiguration $config)
    {
        $this->client = $client;
        $this->setTtl($config->get('ttl'));

        if (empty($this->client->getServerList())) {
            $this->client->addServers($config->getServers());
        }

        $this->client->setOptions($config->getOptions());
    }

    public function get($key, $default = null)
    {
        cache_key_check($key);

        // Cannot return get() directly because FALSE is a valid value
        $value = $this->client->get($key);

        return $this->client->getResultCode() === \Memcached::RES_SUCCESS ? $value : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        cache_key_check($key);
        $ttl = cache_ttl($ttl);

        if ($ttl < 0 || $ttl === 0) {
            $this->client->delete($key);

            return true;
        }

        return $this->client->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        cache_key_check($key);
        $this->client->delete($key);
        $code = $this->client->getResultCode();

        if (\Memcached::RES_NOTFOUND === $code) {
            return true;
        }

        return \Memcached::RES_SUCCESS === $code;
    }

    public function clear()
    {
        return $this->client->flush();
    }

    public function has($key)
    {
        cache_key_check($key);

        // Memcached does not have exists() or similar method
        $this->client->get($key);

        return $this->client->getResultCode() === \Memcached::RES_SUCCESS;
    }

    /*
     * Overrides
     *
     */
    private function multiGet(array $keys, $default = null): iterable
    {
        return array_replace(array_fill_keys($keys, $default), $this->client->getMulti($keys) ?: []);
    }

    private function multiSet(array $values, $ttl = null): bool
    {
        return $this->client->setMulti($values, cache_ttl($ttl));
    }

    private function multiDelete(array $keys): bool
    {
        /** @noinspection PhpParamsInspection */
        return count($keys) === count(array_filter($this->client->deleteMulti($keys)));
    }
}
