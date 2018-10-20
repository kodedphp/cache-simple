<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Interfaces\Serializer;
use PHPUnit\Framework\TestCase;

class RedisWithOnlyJsonSerializerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_redis_client()
    {
        $this->assertInstanceOf(\Redis::class, $this->cache->client());
    }

    /**
     * @dataProvider simpleData
     *
     * @param $data
     */
    public function test_set_multiple_values($data)
    {
        $this->cache->setMultiple($data);

        $lostArray = new \stdClass;
        $lostArray->bar = true;

        $lostObject = new \stdClass;
        $lostObject->foo = 'bar';

        $this->assertSame('foo', $this->cache->get('key1'));
        $this->assertSame(false, $this->cache->get('key2'));

        $this->assertEquals($lostArray, $this->cache->get('key3'),
            'The serialized associative array is unserialized as stdClass');
        $this->assertEquals($lostObject, $this->cache->get('key4'),
            'The serialized Arguments() object is lost and unserialized as stdClass');

        $data['key3'] = $lostArray;
        $data['key4'] = $lostObject;

        $this->assertEquals($data, $this->cache->getMultiple(['key1', 'key2', 'key3', 'key4', 'key5']));
    }

    /**
     * @overridden
     * @dataProvider simpleData
     */
    public function test_should_store_and_retrieve_the_same_cache_item($data)
    {
        $this->assertTrue($this->cache->set('foo', $data));
        $this->assertNotEquals($data, $this->cache->get('foo'),
            'The unserialized data is not the same as the original');

        $this->assertEquals(['foo', 'bar', 'baz'], $this->cache->get('foo')->key5,
            'PHP indexed array is correctly decoded');

        $this->assertNotEquals(['foo' => 'bar'], $this->cache->get('foo')->key4,
            'PHP associative array is NOT correctly decoded');
        $this->assertInstanceOf(\stdClass::class, $this->cache->get('foo')->key4,
            'json_encode() created JS object from PHP assoc array');
    }


    protected function setUp()
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        $this->cache = (new CacheClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'serializer' => Serializer::JSON,
        ])))->build();
    }
}