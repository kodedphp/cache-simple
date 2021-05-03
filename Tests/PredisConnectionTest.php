<?php

namespace Tests\Koded\Caching;

use Koded\Caching\Cache;
use Koded\Caching\CacheException;
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
        try {
            $cache = (new ClientFactory(new ConfigFactory([
                'serializer' => Serializer::JSON,
                'prefix' => new \stdClass(), // invalid?

                'host' => getenv('REDIS_SERVER_HOST'),
                'port' => getenv('REDIS_SERVER_PORT'),

            ])))->new();
            $cache->client()->connect();

            $this->assertTrue($cache->client()->isConnected(), 'The invalid prefix is ignored');
        } catch (CacheException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    public function test_predis_auth_exception()
    {
        try {
            $this->expectException(CacheException::class);
            $this->expectExceptionCode(0);
            // FIXME $this->expectExceptionMessage('ERR Client sent AUTH, but no password is set');

            $cache = (new ClientFactory(new ConfigFactory([
                'auth' => 'fubar',

                'host' => getenv('REDIS_SERVER_HOST'),
                'port' => getenv('REDIS_SERVER_PORT'),

            ])))->new();
            $cache->client()->connect();
        } catch (CacheException $e) {
            $this->markTestSkipped($e->getMessage());
        }
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
