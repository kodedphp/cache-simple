<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use Psr\SimpleCache\CacheInterface;

class RedisClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Memcached extension is not loaded.');
        }
        return simple_cache_factory('redis', [
            'host' => getenv('REDIS_SERVER_HOST'),
        ]);
    }

    protected function setUp(): void
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        parent::setUp();
        $this->cache->clear();

        $this->loadGlobalSkippedTests();
    }
}
