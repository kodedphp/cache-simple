<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use Psr\SimpleCache\CacheInterface;

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
