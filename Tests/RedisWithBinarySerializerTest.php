<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Interfaces\Serializer;
use PHPUnit\Framework\TestCase;

class RedisWithBinarySerializerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    /**
     * @dataProvider simpleData
     *
     * @param $data
     */
    public function test_get_multi_with_default_value($data)
    {
        $this->cache->setMultiple($data);

        $this->assertSame([
            'key1' => 'foo',
            'non-existent-key' => 'with default value',
        ], $this->cache->getMultiple(['key1', 'non-existent-key'], 'with default value'));
    }

    public function test_should_return_redis_client()
    {
        $this->assertInstanceOf(\Redis::class, $this->cache->client());
    }

    protected function setUp(): void
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        if (false === extension_loaded('igbinary')) {
            $this->markTestSkipped('"igbinary" extension is not loaded.');
        }

        $this->cache = (new CacheClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

            'serializer' => Serializer::PHP,
        ])))->new();

        $this->cache->clear();
    }
}