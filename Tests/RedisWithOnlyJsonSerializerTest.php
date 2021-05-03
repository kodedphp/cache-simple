<?php

namespace Tests\Koded\Caching;

use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Serializer;
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
        $saved = $this->cache->setMultiple($data);
        $this->assertTrue($saved);
        $this->assertSame('foo', $this->cache->get('key1'));
        $this->assertSame(false, $this->cache->get('key2'));

        // arrays, objects are lost

        $lostArray = new \stdClass;
        $lostArray->bar = true;

        $lostObject = new \stdClass;
        $lostObject->foo = 'bar';

        $this->assertEquals($lostArray, $this->cache->get('key3'),
            'The serialized associative array is unserialized as stdClass');

        $this->assertEquals($lostObject, $this->cache->get('key4'),
            'The serialized Arguments() object is lost and unserialized as stdClass');
    }

    /**
     * @overridden
     * @dataProvider simpleData
     */
    public function test_should_store_and_retrieve_the_same_cache_item($data)
    {
        $result = $this->cache->set('foo', $data);
        $this->assertTrue($result);

        $this->assertInstanceOf(\stdClass::class, $this->cache->get('foo'),
            'JSON unserialized data is now stcClass (because original is associative array)');

        $this->assertEquals(['foo', 'bar', 'baz'], $this->cache->get('foo')->key5,
            'PHP indexed array is correctly unserialized');

        $this->assertNotEquals(['foo' => 'bar'], $this->cache->get('foo')->key4,
            'PHP associative array is unserialized into stdClass');

        $this->assertInstanceOf(\stdClass::class, $this->cache->get('foo')->key4,
            'json_encode() creates JS object from PHP assoc array');
    }


    protected function setUp(): void
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        $this->cache = (new ClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

            'serializer' => Serializer::JSON,
            'binary' => false,

        ])))->new('redis');

        $this->cache->clear();
    }
}