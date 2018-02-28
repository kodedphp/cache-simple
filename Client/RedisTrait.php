<?php

namespace Koded\Caching\Client;

/**
 * @property \Redis client
 */
trait RedisTrait
{

    public function getMultiple($keys, $default = null)
    {
        $cached = [];
        foreach ($keys as $key) {
            $cached[$key] = $this->get($key, $default);
        }

        return $cached;
    }

    public function setMultiple($values, $ttl = null)
    {
        $cached = 0;
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl) && ++$cached;
        }

        return count($values) === $cached;
    }

    public function clear()
    {
        return $this->client->flushAll();
    }
}
