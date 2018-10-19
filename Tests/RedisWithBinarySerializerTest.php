<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheFactory;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class RedisWithBinarySerializerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_redis_client()
    {
        $this->assertInstanceOf(\Redis::class, $this->cache->client());
    }

    protected function setUp()
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        if (false === function_exists('igbinary_serialize')) {
            $this->markTestSkipped('"igbinary" extension is not loaded. Redis serializer fallback to PHP');
        }

        $this->cache = (new CacheFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
//            'binary' => true,
            'serializer' => 'igbinary'
        ])))->build();
    }
}