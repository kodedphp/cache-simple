<?php

namespace Koded\Caching\Tests\Integration;

trait SimpleCacheIntegrationTrait
{
    protected function tearDown(): void
    {
        putenv('CACHE_CLIENT=');

        if ($this->cache !== null) {
            $this->cache->clear();
        }

        $this->cache = null;
    }

    /** overwritten */
    public static function invalidKeys()
    {
        return self::removeColumnFromInvalidKeys();
    }

    /** overwritten */
    public static function invalidArrayKeys()
    {
        return self::removeColumnFromInvalidKeys();
    }

    /**
     * Data provider for invalid keys.
     * Allows ":" in the cache key name.
     *
     * @return array
     */
    private static function removeColumnFromInvalidKeys(): array
    {
        $keys = parent::invalidKeys();
        unset($keys[14]);

        return $keys;
    }


}