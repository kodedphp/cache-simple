<?php

namespace Tests\Koded\Caching\Integration;

trait SimpleCacheIntegrationTrait
{
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

    protected function tearDown(): void
    {
        putenv('CACHE_CLIENT=');

        if ($this->cache !== null) {
            $this->cache->clear();
        }

        $this->cache = null;
    }

    private function loadGlobalSkippedTests(): void
    {
        $this->skippedTests['testSetMultipleWithIntegerArrayKey'] = 'string-numeric key is type casted internally to integer';
    }
}
