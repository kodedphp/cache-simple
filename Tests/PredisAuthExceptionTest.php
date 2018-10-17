<?php

namespace Koded\Caching;

use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class PredisAuthExceptionTest extends TestCase
{

    public function test_should_throw_exception_if_auth_is_provided_but_redis_does_not_requre_it()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_PHP_EXCEPTION);
        $this->expectExceptionMessage('[Cache Exception] ERR Client sent AUTH, but no password is set');

        putenv('CACHE_CLIENT=predis');

        (new ClientFactory(new ConfigFactory([
            'auth' => 'fubar',

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),
            'options' => [
                'prefix' => 'test:'
            ],
        ])))->build();
    }
}
