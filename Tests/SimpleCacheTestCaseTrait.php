<?php

namespace Koded\Caching;

use Koded\Stdlib\Arguments;
use Psr\SimpleCache\CacheInterface;

trait SimpleCacheTestCaseTrait
{

    /** @var Cache */
    protected $cache;

    protected function tearDown()
    {
        putenv('CACHE_CLIENT=');

        if ($this->cache) {
            $this->cache->clear();
        }

        $this->cache = null;
    }

    public function test_client_should_implement_cache_interface()
    {
        $this->assertInstanceOf(CacheInterface::class, $this->cache);
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
            'non-existent-key' => 'with default value',
        ], $this->cache->getMultiple(['key1', 'non-existent-key'], 'with default value'));
    }

    /**
     * @dataProvider simpleData
     *
     * @param $data
     */
    public function test_delete_multi_with_non_existent_key($data)
    {
        $this->cache->setMultiple($data);
        $this->assertTrue($this->cache->deleteMultiple(['key1', 'non-existent-key']));
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
                    'key5' => ['foo', 'bar', 'baz'],
                    'key6' => null,
                ]
            ]
        ];
    }
}
