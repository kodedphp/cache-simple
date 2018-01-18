<?php

namespace Koded\Caching;

use Koded\Caching\Client\RedisClient;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;
use Redis;

class RedisWithJsonSerializerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_redis_client()
    {
        $this->assertInstanceOf(Redis::class, $this->cache->client());
    }

    public function test_should_return_redis_instance()
    {
        $this->assertInstanceOf(RedisClient::class, $this->cache->instance());
    }

    protected function setUp()
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        $this->cache = new SimpleCache((new ClientFactory(new ConfigFactory([
            'serializer' => Cache::SERIALIZER_JSON,

            'host' => getenv('REDIS_SERVER_HOST'),
            'binary' => true,
        ])))->build());
    }
}