<?php

namespace Tests\Koded\Caching\Integration;

use Cache\IntegrationTests\SimpleCacheTest;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

/**
 * @group integration
 */
class MemoryClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        $this->loadGlobalSkippedTests();

        return simple_cache_factory('memory');
    }
}
