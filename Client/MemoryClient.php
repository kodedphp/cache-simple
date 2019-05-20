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
use function Koded\Caching\verify_key;
use function Koded\Stdlib\now;


/**
 * @property MemoryClient client
 */
final class MemoryClient implements Cache
{
    use ClientTrait, MultiplesTrait;

    private $storage = [];
    private $expiration = [];


    public function __construct(int $ttl = null)
    {
        $this->ttl = $ttl;
    }


    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->storage[$key] : $default;
    }


    public function set($key, $value, $ttl = null)
    {
        verify_key($key);

        if (1 > $expiration = $this->timestampWithGlobalTtl($ttl, Cache::DATE_FAR_FAR_AWAY)) {
            unset($this->storage[$key], $this->expiration[$key]);
        } else {
            // Loose the reference to the object
            $this->storage[$key] = is_object($value) ? clone $value : $value;
            $this->expiration[$key] = $expiration;
        }

        return true;
    }


    public function delete($key)
    {
        if (false === $this->has($key)) {
            return true;
        }

        unset($this->storage[$key], $this->expiration[$key]);

        return true;
    }


    public function clear()
    {
        $this->storage = [];
        $this->expiration = [];

        return true;
    }


    public function has($key)
    {
        verify_key($key);

        if (false === array_key_exists($key, $this->expiration)) {
            return false;
        }

        if ($this->expiration[$key] <= now()->getTimestamp()) {
            unset($this->storage[$key], $this->expiration[$key]);

            return false;
        }

        return true;
    }


    public function getExpirationFor(string $key): ?int
    {
        return $this->expiration[$key] ?? null;
    }
}
