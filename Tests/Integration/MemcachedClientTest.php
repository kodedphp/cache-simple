<?php

namespace Tests\Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Tests\Koded\Caching\Integration\SimpleCacheIntegrationTrait;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

class MemcachedClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        if (false === extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension is not loaded.');
        }
        return simple_cache_factory('memcached');
    }

    protected function setUp(): void
    {
        if (false === extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension is not loaded.');
        }

        if (getenv('CI')) {
            putenv('MEMCACHED_POOL=[["127.0.0.1", 11211]]');
        } else {
            putenv('MEMCACHED_POOL=[["memcached", 11211]]');
        }

        parent::setUp();
        $this->cache->clear();

        $this->skippedTests = [
            'testBasicUsageWithLongKey' => 'Memcached max key length is 250 chars',
        ];

        $this->loadGlobalSkippedTests();
    }
}
