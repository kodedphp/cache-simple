<?php

namespace Tests\Koded\Caching;

use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Serializer;
use PHPUnit\Framework\TestCase;

class RedisWithJsonSerializerTest extends TestCase
{
    use SimpleCacheTestCaseTrait;

    public function test_should_return_redis_client()
    {
        $this->assertInstanceOf(\Redis::class, $this->cache->client());
    }

    protected function setUp(): void
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        $this->cache = (new ClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),
            'serializer' => Serializer::JSON,

            'options' => [
                JSON_NUMERIC_CHECK
            ],

        ])))->new();

        $this->cache->clear();
    }
}