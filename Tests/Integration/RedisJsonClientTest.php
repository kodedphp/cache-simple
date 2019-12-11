<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use Koded\Stdlib\Interfaces\Serializer;
use Psr\SimpleCache\CacheInterface;

class RedisJsonClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        return simple_cache_factory('redis', [
            'host' => getenv('REDIS_SERVER_HOST'),
            'serializer' => Serializer::JSON,
            'binary' => Serializer::PHP
        ]);
    }

    protected function setUp(): void
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        parent::setUp();
        $this->cache->clear();
    }
}
