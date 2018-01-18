<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class PredisConnectionTest extends TestCase
{

    public function test_should_throw_exception_on_connection_error()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_CONNECTION_ERROR);
        $this->expectExceptionMessage('[Cache Exception] Failed to connect the Predis client');

        putenv('CACHE_CLIENT=predis');

        new SimpleCache((new ClientFactory(new ConfigFactory([
            'host' => 'invalid-redis-host'
        ])))->build());
    }
}
