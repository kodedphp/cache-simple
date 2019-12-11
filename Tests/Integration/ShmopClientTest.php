<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;

class ShmopClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    public function createSimpleCache()
    {
        return simple_cache_factory('shmop');
    }

    protected function setup()
    {
        if (false === extension_loaded('shmop')) {
            $this->markTestSkipped('shmop extension is not loaded.');
        }

        parent::setUp();
        $this->cache->clear();
    }
}
