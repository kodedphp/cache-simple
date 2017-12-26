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
        $this->expectExceptionMessage('Invalid cache serializer "junk"');
        $this->expectExceptionCode(7);

        putenv('CACHE_CLIENT=redis');

        new SimpleCache((new ClientFactory(new ConfigFactory([
            'serializer' => 'junk',
            'host' => getenv('REDIS_SERVER_HOST'),
        ])))->build());
    }
}
