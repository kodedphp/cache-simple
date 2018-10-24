<?php

namespace Koded\Caching\Tests\Integration;

use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

class RedisClientTest extends SimpleCacheIntegrationTest
{

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        return simple_cache_factory('redis', [
            'host' => getenv('REDIS_SERVER_HOST'),
        ]);
    }

    protected function setUp()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        parent::setUp();
    }
}
