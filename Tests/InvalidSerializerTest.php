<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class InvalidSerializerTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function test_should_fail_on_invalid_serializer_in_configuration()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionCode(Cache::E_INVALID_SERIALIZER);
        $this->expectExceptionMessage('Invalid cache serializer "junk"');

        putenv('CACHE_CLIENT=redis');

        new SimpleCache((new ClientFactory(new ConfigFactory([
            'serializer' => 'junk',
            'host' => getenv('REDIS_SERVER_HOST'),
        ])))->build());
    }

    protected function setUp()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }
    }
}
