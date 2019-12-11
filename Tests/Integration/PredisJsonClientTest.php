<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use Koded\Stdlib\Interfaces\Serializer;
use Psr\SimpleCache\CacheInterface;

class PredisJsonClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        return simple_cache_factory('predis', [
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

            'serializer' => Serializer::JSON,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache->clear();
    }
}
