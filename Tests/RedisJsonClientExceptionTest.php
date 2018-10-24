<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Interfaces\Serializer;
use PHPUnit\Framework\TestCase;

class RedisJsonClientExceptionTest extends TestCase
{

    public function test_should_throw_exception_on_invalid_option()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_PHP_EXCEPTION);
        $this->expectExceptionMessage('[Cache Exception] Redis::setOption() expects parameter 2 to be string, object given');

        putenv('CACHE_CLIENT=redis');

        (new CacheClientFactory(new ConfigFactory([
            'serializer' => Serializer::JSON,
            'prefix' => new \stdClass(), // invalid prefix to test the catch block

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

        ])))->build();
    }
}
