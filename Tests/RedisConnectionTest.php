<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheFactory;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class RedisConnectionTest extends TestCase
{

    public function test_should_throw_exception_on_connection_error()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_CONNECTION_ERROR);
        $this->expectExceptionMessage('[Cache Exception] Failed to connect the Redis client');

        putenv('CACHE_CLIENT=redis');

        (new CacheFactory(new ConfigFactory([
            'host' => 'invalid-redis-host'
        ])))->build();
    }

    protected function setUp()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }
    }
}
