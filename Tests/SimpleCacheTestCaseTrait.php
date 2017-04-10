<?php

namespace Koded\Caching;

use Psr\SimpleCache\CacheInterface;

trait SimpleCacheTestCaseTrait
{

    /** @var SimpleCache */
    protected $cache;

    public function test_client_should_implement_cache_interface()
    {
        $this->assertAttributeInstanceOf(CacheInterface::class, 'client', $this->cache);
    }

    public function test_get_has_and_set_methods()
    {
        $this->assertFalse($this->cache->has('foo'));

        $this->cache->set('foo', 'lorem ipsum');
        $this->assertSame('lorem ipsum', $this->cache->get('foo'));
    }

    public function test_get_with_default_value()
    {
        $this->assertSame('default value', $this->cache->get('non-existent-key', 'default value'));
    }

    /**
     * @dataProvider simpleData
     *
     * @param $data
     */
    public function test_delete($data)
    {
        $this->cache->setMultiple($data);
        $this->assertTrue($this->cache->has('key1'));

        $this->assertTrue($this->cache->delete('key1'));
        $this->assertFalse($this->cache->has('key1'));
    }

    /**
     * @dataProvider simpleData
     *
     * @param $data
     */
    public function test_set_multiple_values($data)
    {
        $this->cache->setMultiple($data);

        $this->assertSame('foo', $this->cache->get('key1'));
        $this->assertSame(false, $this->cache->get('key2'));
        $this->assertSame(['bar' => true], $this->cache->get('key3'));

        $this->assertSame($data, $this->cache->getMultiple(['key1', 'key2', 'key3']));
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
     *
     * @param $data
     */
    public function test_set_multi_with_expired_value($data)
    {
        $this->cache->setMultiple($data, 0);

        $this->assertSame([
            'key1' => 'default value',
            'key2' => 'default value',
            'key3' => 'default value',
            'non-existent-key' => 'default value',
        ], $this->cache->getMultiple(['key1', 'key2', 'key3', 'non-existent-key'], 'default value'));
    }

    /**
     * @dataProvider simpleData
     *
     * @param $data
     */
    public function test_delete_multiple_values($data)
    {
        $this->cache->setMultiple($data);

        $this->assertTrue($this->cache->deleteMultiple(['key1', 'key3']));

        $this->assertSame([
            'key1' => 'default value',
            'key2' => false,
            'key3' => 'default value',

        ], $this->cache->getMultiple(['key1', 'key2', 'key3'], 'default value'));
    }

    /**
     * @dataProvider simpleData
     *
     * @param $data
     */
    public function test_clear($data)
    {
        $this->cache->setMultiple($data);
        $this->assertTrue($this->cache->clear());

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }

    public function test_expired_cache()
    {
        $this->assertTrue($this->cache->set('foo', 'bar', 1));

        // at this point the cached item exists
        $this->assertTrue($this->cache->has('foo'));

        // but after some time it is deleted
        sleep(2);

        $this->assertSame('expired', $this->cache->get('foo', 'expired'));
        $this->assertFalse($this->cache->has('foo'));
    }

    public function test_should_expire_the_item_for_negative_ttl()
    {
        $this->assertFalse($this->cache->has('key1'));

        $this->assertTrue($this->cache->set('foo', 'bar', -1));
        $this->assertFalse($this->cache->has('foo'));
    }

    public function test_should_expire_the_item_for_zero_ttl()
    {
        $this->assertFalse($this->cache->has('key1'));

        $this->assertTrue($this->cache->set('foo', 'bar', 0));
        $this->assertFalse($this->cache->has('foo'));
    }

    public function test_should_return_memcached_instance()
    {
        $this->assertNotNull($this->cache->client());
    }

    public function simpleData()
    {
        return [
            [
                [
                    'key1' => 'foo',
                    'key2' => false,
                    'key3' => ['bar' => true]
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