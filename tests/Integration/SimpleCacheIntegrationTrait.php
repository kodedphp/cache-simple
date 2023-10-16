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

    protected function loadGlobalSkippedTests(): void
    {
        $this->skippedTests['testSetMultipleWithIntegerArrayKey'] = 'string-numeric key is type casted internally to integer';
        $this->skippedTests['testGetInvalidKeys'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testSetInvalidKeys'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testHasInvalidKeys'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testDeleteInvalidKeys'] = 'strict argument type; test makes no sense';

        $this->skippedTests['testSetInvalidTtl'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testSetMultipleInvalidTtl'] = 'strict argument type; test makes no sense';

        $this->skippedTests['testGetMultipleNoIterable'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testSetMultipleNoIterable'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testDeleteMultipleNoIterable'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testSetMultipleInvalidKeys'] = 'strict argument type; test makes no sense';
        $this->skippedTests['testDeleteMultipleInvalidKeys'] = 'strict argument type; test makes no sense';
    }
}
