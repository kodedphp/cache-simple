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
use function Koded\Caching\{cache_key_check, cache_ttl};

final class MemoryClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    private $storage = [];
    private $expiration = [];

    public function __construct(?int $ttl)
    {
        $this->setTtl($ttl ?? Cache::A_DATE_FAR_FAR_AWAY);
    }

    public function get($key, $default = null)
    {
        cache_key_check($key);

        if (isset($this->expiration[$key]) && $this->expiration[$key] < time()) {
            $this->delete($key);

            return $default;
        }

        return $this->storage[$key] ?? $default;
    }

    public function set($key, $value, $ttl = null)
    {
        cache_key_check($key);

        if ($ttl < 0 || $ttl === 0) {
            return $this->delete($key);
        }

        if (null === $ttl) {
            $this->expiration[$key] = time() + $this->ttl;
        } else {
            $this->expiration[$key] = time() + cache_ttl($ttl);
        }

        $this->storage[$key] = $value;

        return true;
    }

    public function delete($key)
    {
        cache_key_check($key);
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
        cache_key_check($key);

        return array_key_exists($key, $this->storage);
    }
}
