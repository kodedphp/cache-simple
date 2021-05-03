<?php

namespace Tests\Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\CacheException;
use Tests\Koded\Caching\Integration\SimpleCacheIntegrationTrait;
use Koded\Stdlib\Serializer;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

class PredisJsonClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        try {
            $client = simple_cache_factory('predis', [
                'host' => getenv('REDIS_SERVER_HOST'),
                'port' => getenv('REDIS_SERVER_PORT'),

                'serializer' => Serializer::JSON,
            ]);
            $client->client()->connect();
            return $client;
        } catch (CacheException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache->clear();

        $this->loadGlobalSkippedTests();
    }
}
