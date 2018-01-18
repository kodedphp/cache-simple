<?php

namespace Koded\Caching;

use Koded\Caching\Client\RedisClient;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class RedisAuthExceptionTest extends TestCase
{

    public function test_should_throw_exception_on_invalid_option()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_PHP_EXCEPTION);
        $this->expectExceptionMessage('[Cache Exception] Redis::setOption() expects parameter 2 to be string, object given');

        putenv('CACHE_CLIENT=redis');

        (new ClientFactory(new ConfigFactory([
            'prefix' => new \stdClass(), // some crap prefix to test the catch block

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

        ])))->build();
    }
}
