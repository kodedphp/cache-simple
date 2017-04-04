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

use Koded\Caching\Configuration\MemcachedConfiguration;
use Memcached;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\cache_ttl;

class MemcachedClient implements CacheInterface
{

    /** @var Memcached */
    private $client;

    public function __construct(MemcachedConfiguration $config)
    {
        $this->client = new Memcached($config->get('id'));
        $this->client->setOptions($config->getOptions());

        if (empty($this->client->getServerList())) {
            $this->client->addServers($config->getServers());
        }
    }

    public function get($key, $default = null)
    {
        // Cannot use get() directly because FALSE is a valid value
        $value = $this->client->get($key);

        return $this->client->getResultCode() === Memcached::RES_SUCCESS ? $value : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        if ($ttl < 0 or $ttl === 0) {
            $this->client->delete($key);

            return true;
        }

        return $this->client->set($key, $value, cache_ttl($ttl));
    }

    public function delete($key)
    {
        return $this->client->delete($key);
    }

    public function clear()
    {
        return $this->client->flush();
    }

    public function getMultiple($keys, $default = null)
    {
        return array_replace(array_fill_keys($keys, $default), $this->client->getMulti($keys) ?: []);
    }

    public function setMultiple($values, $ttl = null)
    {
        if ($ttl < 0 or $ttl === 0) {
            return $this->deleteMultiple(array_keys($values));
        }

        return $this->client->setMulti($values, cache_ttl($ttl));
    }

    public function deleteMultiple($keys)
    {
        /** @noinspection PhpParamsInspection */
        return count($keys) === count(array_filter($this->client->deleteMulti($keys)));
    }

    public function has($key)
    {
        // Memcached does not have exists, has or similar method
        $this->client->get($key);

        return $this->client->getResultCode() === Memcached::RES_SUCCESS;
    }
}