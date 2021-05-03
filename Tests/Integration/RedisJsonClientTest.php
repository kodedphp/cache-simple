<?php

namespace Tests\Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\CacheException;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use Koded\Stdlib\Serializer;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

class RedisJsonClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        try {
            return simple_cache_factory('redis', [
                'host' => getenv('REDIS_SERVER_HOST'),
                'serializer' => Serializer::JSON,
                'binary' => Serializer::PHP
            ]);
        } catch (CacheException $e) {
            $this->markTestSkipped($e->getMessage());
        }
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
