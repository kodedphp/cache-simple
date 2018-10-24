<?php

namespace Koded\Caching;

use Koded\Stdlib\Arguments;
use Psr\SimpleCache\CacheInterface;

trait SimpleCacheTestCaseTrait
{

    /** @var Cache */
    protected $cache;

    public function test_client_should_implement_cache_interface()
    {
        $this->assertInstanceOf(CacheInterface::class, $this->cache);
    }

    public function test_should_return_cache_instance()
    {
        $this->assertNotNull($this->cache->client());
    }

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
            'non-existent-key' => 'default value',
        ], $this->cache->getMultiple(['key1', 'non-existent-key'], 'default value'));
    }

    /**
     * @dataProvider simpleData
     */
    public function test_should_store_and_retrieve_the_same_cache_item($data)
    {
        $this->assertTrue($this->cache->set('foo', $data));
        $this->assertEquals($data, $this->cache->get('foo'));
    }

    public function simpleData()
    {
        return [
            [
                [
                    'key1' => 'foo',
                    'key2' => false,
                    'key3' => ['bar' => true],
                    'key4' => new Arguments(['foo' => 'bar']),
                    'key5' => ['foo', 'bar', 'baz']
                ]
            ]
        ];
    }

    protected function tearDown()
    {
        $this->cache->clear();
        putenv('CACHE_CLIENT=');
    }
}
