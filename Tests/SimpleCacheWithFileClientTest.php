<?php

namespace Koded\Caching;

use Koded\Caching\Client\FileClient;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

class SimpleCacheWithFileClientTest extends TestCase
{

    /** @var SimpleCache */
    private $cache;

    public function testClientShouldImplementCacheInterface()
    {
        $this->assertAttributeInstanceOf(CacheInterface::class, 'client', $this->cache);
    }

    public function testShouldCreateFileClientWithoutConfiguration()
    {
        $this->assertAttributeEquals(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'dir', new FileClient([], new NullLogger));
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
        $this->assertTrue($this->cache->set('foo', 'bar', -1));

        // at this point the cached item exists
        $this->assertTrue($this->cache->has('foo'));

        // but after get() it is deleted
        $this->assertSame('expired', $this->cache->get('foo', 'expired'));
        $this->assertFalse($this->cache->has('foo'));
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

    protected function setUp()
    {
        $dir = vfsStream::setup();
        $this->cache = new SimpleCache(new FileClient(['dir' => $dir->url()], new NullLogger));
    }
}
