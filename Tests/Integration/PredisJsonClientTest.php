<?php

namespace Koded\Caching\Tests\Integration;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Stdlib\Interfaces\Serializer;
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
        $this->markTestSkipped();

        return simple_cache_factory('predis', [
            'host' => getenv('REDIS_SERVER_HOST'),
            'serializer' => Serializer::JSON,
            'binary' => Serializer::PHP
        ]);
    }
}
