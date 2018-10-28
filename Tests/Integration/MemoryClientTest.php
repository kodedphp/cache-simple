<?php

namespace Koded\Caching\Tests\Integration;

use Cache\IntegrationTests\SimpleCacheTest;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

class MemoryClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        return simple_cache_factory('memory');
    }
}
