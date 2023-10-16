<?php

namespace Tests\Koded\Caching\Integration;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\CacheException;
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

/**
 * @group integration
 */
class PredisClientTest extends SimpleCacheTest
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
