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

use Psr\SimpleCache\CacheInterface;

final class MemoryClient implements CacheInterface
{

    use ClientTrait, MultiplesTrait;

    private $storage = [];
    private $expiration = [];

    public function get($key, $default = null)
    {
        if (isset($this->expiration[$key]) && $this->expiration[$key] < time()) {
            $this->delete($key);

            return $default;
        }

        return $this->storage[$key] ?? $default;
    }

    public function set($key, $value, $ttl = null)
    {
        if ($ttl < 0 || $ttl === 0) {
            return $this->delete($key);
        }

        if ($ttl > 0) {
            $this->expiration[$key] = time() + $ttl;
        }

        $this->storage[$key] = $value;

        return true;
    }

    public function delete($key)
    {
        unset($this->storage[$key], $this->expiration[$key]);

        return false === array_key_exists($key, $this->storage);
    }

    public function clear()
    {
        $this->storage = [];
        $this->expiration = [];

        return true;
    }

    public function deleteMultiple($keys)
    {
        $deleted = 0;
        foreach ($keys as $key) {
            $this->delete($key) && ++$deleted;
        }

        return count($keys) === $deleted;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->storage);
    }
}
