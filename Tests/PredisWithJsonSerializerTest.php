<?php

namespace Tests\Koded\Caching;

use Koded\Caching\CacheException;
use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Serializer;
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

        try {
            $this->cache = (new ClientFactory(new ConfigFactory([
                'host' => getenv('REDIS_SERVER_HOST'),
                'port' => getenv('REDIS_SERVER_PORT'),

                'serializer' => Serializer::JSON,

            ])))->new();
        } catch (CacheException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->cache->clear();
    }
}
