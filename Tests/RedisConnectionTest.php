<?php

namespace Tests\Koded\Caching;

use Koded\Caching\Cache;
use Koded\Caching\CacheException;
use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Serializer;
use PHPUnit\Framework\TestCase;

class RedisConnectionTest extends TestCase
{
    public function test_should_throw_exception_on_connection_error()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_CONNECTION_ERROR);
        $this->expectExceptionMessage('[Cache Exception] Failed to connect the Redis client');

        (new ClientFactory(new ConfigFactory([
            'host' => 'invalid-redis-host'
        ])))->new('redis');
    }

    public function test_redis_invalid_option_exception()
    {
        $this->expectException(CacheException::class);

        if (!getenv('CI')) {
            $this->expectExceptionCode(2);
            $this->expectExceptionMessage('Redis::setOption() expects parameter 2 to be string, object given');
        }

        (new ClientFactory(new ConfigFactory([
            'serializer' => Serializer::JSON,
            'prefix' => new \stdClass(), // invalid prefix to test the catch block

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

        ])))->new();
    }

    public function test_redis_auth_exception()
    {
        $redis = (new ClientFactory(new ConfigFactory([
            'auth' => 'fubar',

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT')

        ])))->new();

        $this->assertTrue($redis->client()->isConnected(), 'The auth is ignored, even it is not set in Redis');
    }

    protected function setUp(): void
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        putenv('CACHE_CLIENT=redis');
    }

    protected function tearDown(): void
    {
        putenv('CACHE_CLIENT=');
    }
}
