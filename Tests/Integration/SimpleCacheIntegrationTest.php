<?php

namespace Koded\Caching\Tests\Integration;

use Cache\IntegrationTests\SimpleCacheTest;

abstract class SimpleCacheIntegrationTest extends SimpleCacheTest
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
}