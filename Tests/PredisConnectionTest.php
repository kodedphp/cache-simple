<?php

namespace Koded\Caching;

use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Serializer;
use PHPUnit\Framework\TestCase;

class PredisConnectionTest extends TestCase
{
    public function test_should_throw_exception_on_connection_error()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_CONNECTION_ERROR);
        $this->expectExceptionMessage('[Cache Exception] Failed to connect the Predis client');

        (new ClientFactory(new ConfigFactory([
            'host' => 'invalid-redis-host'
        ])))->new();
    }

    public function test_predis_invalid_option_exception()
    {
        $cache = (new ClientFactory(new ConfigFactory([
            'serializer' => Serializer::JSON,
            'prefix' => new \stdClass(), // invalid?

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

        ])))->new();

        $this->assertTrue($cache->client()->isConnected(), 'The invalid prefix is ignored');
    }

    public function test_predis_auth_exception()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(0);
        // FIXME $this->expectExceptionMessage('ERR Client sent AUTH, but no password is set');

        (new ClientFactory(new ConfigFactory([
            'auth' => 'fubar',

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

        ])))->new();
    }

    protected function setUp(): void
    {
        putenv('CACHE_CLIENT=predis');
    }

    protected function tearDown(): void
    {
        putenv('CACHE_CLIENT=');
    }
}
