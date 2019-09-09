<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Interfaces\Serializer;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class PredisWithJsonSerializerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_predis_client()
    {
        $this->assertInstanceOf(Client::class, $this->cache->client());
    }

    protected function setUp(): void
    {
        putenv('CACHE_CLIENT=predis');

        $this->cache = (new CacheClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

            'serializer' => Serializer::JSON,

        ])))->new();

        $this->cache->clear();
    }
}
