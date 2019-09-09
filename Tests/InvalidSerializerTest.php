<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Exceptions\SerializerException;
use PHPUnit\Framework\TestCase;

class InvalidSerializerTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function test_should_fail_on_invalid_serializer_in_configuration()
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage('Failed to create a serializer for "junk"');

        putenv('CACHE_CLIENT=redis');

        (new CacheClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'serializer' => 'junk'
        ])))->new();
    }

    protected function setUp(): void
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }
    }
}
