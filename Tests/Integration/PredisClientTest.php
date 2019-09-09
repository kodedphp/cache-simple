<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use Psr\SimpleCache\CacheInterface;

class PredisClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        $this->skippedTests = [
            'testSetMultipleInvalidKeys' => '',
        ];

        return simple_cache_factory('predis', [
            'host' => getenv('REDIS_SERVER_HOST'),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache->clear();
    }
}
