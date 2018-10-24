<?php

namespace Koded\Caching\Tests\Integration;

use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

class PredisClientTest extends SimpleCacheIntegrationTest
{

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        return simple_cache_factory('predis', [
            'host' => getenv('REDIS_SERVER_HOST'),
        ]);
    }
}
