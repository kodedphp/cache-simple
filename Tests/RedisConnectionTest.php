<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class RedisConnectionTest extends TestCase
{

    public function test_should_throw_exception_on_connection_error()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('[Cache Exception] Redis::connect()');

        putenv('CACHE_CLIENT=redis');
        putenv('REDIS_HOST=invalid-redis-host');

        new SimpleCache((new ClientFactory(new ConfigFactory))->build());
    }
}
