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

        unset($keys[15]); // allow ":" in the key name

        return $keys;
    }

    protected function tearDown()
    {
        putenv('CACHE_CLIENT=');

        if ($this->cache !== null) {
            $this->cache->clear();
        }

        $this->cache = null;
    }
}