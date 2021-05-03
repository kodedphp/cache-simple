<?php

namespace Tests\Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Tests\Koded\Caching\Integration\SimpleCacheIntegrationTrait;
use function Koded\Caching\simple_cache_factory;

class ShmopClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    public function createSimpleCache()
    {
        if (false === extension_loaded('shmop')) {
            $this->markTestSkipped('shmop extension is not loaded.');
        }
        return simple_cache_factory('shmop');
    }

    protected function setUp(): void
    {
        if (false === extension_loaded('shmop')) {
            $this->markTestSkipped('shmop extension is not loaded.');
        }

        parent::setUp();
        $this->cache->clear();

        $this->loadGlobalSkippedTests();
    }
}
