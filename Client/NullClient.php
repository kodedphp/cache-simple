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
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\guard_cache_key;

/**
 * @property NullClient client
 *
 * @codeCoverageIgnore
 */
final class NullClient implements CacheInterface, Cache
{

    use ClientTrait;

    public function get($key, $default = null)
    {
        guard_cache_key($key);

        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        guard_cache_key($key);

        return true;
    }

    public function delete($key)
    {
        guard_cache_key($key);

        return true;
    }

    public function clear()
    {
        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        return array_fill_keys($keys, $default);
    }

    public function setMultiple($values, $ttl = null)
    {
        return true;
    }

    public function deleteMultiple($keys)
    {
        return true;
    }

    public function has($key)
    {
        guard_cache_key($key);

        return false;
    }
}
