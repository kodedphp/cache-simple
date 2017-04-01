<?php

namespace Koded\Caching;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

abstract class SimpleCacheTestCase extends TestCase
{

    /** @var SimpleCache */
    protected $cache;

    public function testClientShouldImplementCacheInterface()
    {
        $this->assertAttributeInstanceOf(CacheInterface::class, 'client', $this->cache);
    }

    public function testGetHasAndSetMethods()
    {
        $this->assertFalse($this->cache->has('foo'));

        $this->cache->set('foo', 'lorem ipsum');
        $this->assertSame('lorem ipsum', $this->cache->get('foo'));
    }

    public function testGetWithDefaultValue()
    {
        $this->assertSame('default value', $this->cache->get('non-existent-key', 'default value'));
    }

    /**
     * @dataProvider simpleData
     * @param $data
     */
    public function testDelete($data)
    {
        $this->cache->setMultiple($data);

        $this->assertTrue($this->cache->has('key1'));
        $this->cache->delete('key1');
        $this->assertFalse($this->cache->has('key1'));
    }

    /**
     * @dataProvider simpleData
     * @param $data
     */
    public function testSetMultipleValues($data)
    {
        $this->cache->setMultiple($data);

        $this->assertSame('foo', $this->cache->get('key1'));
        $this->assertSame(false, $this->cache->get('key2'));
        $this->assertSame(['bar' => true], $this->cache->get('key3'));

        $this->assertSame($data, $this->cache->getMultiple(['key1', 'key2', 'key3']));
    }

    /**
     * @dataProvider simpleData
     * @param $data
     */
    public function testGetMultiWithDefaultValue($data)
    {
        $this->cache->setMultiple($data);

        $this->assertSame([
            'key1' => 'foo',
            'non-existent-key' => 'default value',
        ], $this->cache->getMultiple(['key1', 'non-existent-key'], 'default value'));
    }

    /**
     * @dataProvider simpleData
     * @param $data
     */
    public function testDeleteMultipleValues($data)
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
     * @param $data
     */
    public function testClear($data)
    {
        $this->cache->setMultiple($data);
        $this->assertTrue($this->cache->clear());

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }

    public function testExpiredCache()
    {
        $this->assertTrue($this->cache->set('foo', 'bar', 1));

        // at this point the cached item exists
        $this->assertTrue($this->cache->has('foo'));

        // but after some time it is deleted
        sleep(1);
        $this->assertSame('expired', $this->cache->get('foo', 'expired'));
        $this->assertFalse($this->cache->has('foo'));
    }

    public function testShouldRejectNegativeTtlValue()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Invalid TTL value');

        $this->cache->set('foo', 'bar', -1);
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
}