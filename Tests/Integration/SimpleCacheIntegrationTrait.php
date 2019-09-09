<?php

namespace Koded\Caching\Tests\Integration;

trait SimpleCacheIntegrationTrait
{
    /**
     * Data provider for invalid keys.
     * Allows ":" in the cache key name.
     *
     * @return array
     */
    public static function invalidKeys()
    {
        $keys = parent::invalidKeys();

        unset($keys[14]); // allow ":" in the key name

        return array_values($keys);
    }

    protected function tearDown(): void
    {
        putenv('CACHE_CLIENT=');

        if ($this->cache !== null) {
            $this->cache->clear();
        }

        $this->cache = null;
    }
}