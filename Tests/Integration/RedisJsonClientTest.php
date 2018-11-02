<?php

namespace Koded\Caching\Tests\Integration;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Stdlib\Interfaces\Serializer;
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
        return simple_cache_factory('redis', [
            'host' => getenv('REDIS_SERVER_HOST'),
            'serializer' => Serializer::JSON,
            'binary' => Serializer::PHP
        ]);
    }

    protected function setUp()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        parent::setUp();
        $this->cache->clear();
    }
}
