<?php

namespace Koded\Caching;

use Koded\Caching\Client\MemcachedClient;
use Koded\Caching\Client\NullClient;
use function Koded\Stdlib\dump;
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{

    public function test_should_create_simple_cache_with_null_client()
    {
        $this->assertInstanceOf(SimpleCache::class, cache());
    }

    public function test_should_guard_proper_cache_key()
    {
        $this->assertSame('Proper-Key:1', cache_key_guard('Proper-Key:1'));
    }

    public function test_should_reject_invalid_cache_key()
    {
        $invalidKey = 'w#4T |5 tH1~';
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage(sprintf('The cache key is invalid, "%s" given', $invalidKey));
        cache_key_guard($invalidKey);
    }

    public function test_should_return_the_same_ttl_if_less_then_one()
    {
        $this->assertSame(0, cache_ttl(0));
        $this->assertSame(-1, cache_ttl(-1));
    }

    public function test_should_return_null_if_ttl_is_null()
    {
        $this->assertNull(cache_ttl(null));
    }

    public function test_should_return_the_same_ttl()
    {
        $this->assertSame(100, cache_ttl(100));
    }

    public function test_should_set_ttl_from_date_interval()
    {
        $interval = new \DateInterval('PT42S');
        $this->assertSame(42, cache_ttl($interval));
    }

    public function test_should_create_new_simplecache_instance_with_null_client()
    {
        $cache = simple_cache_factory();
        $this->assertAttributeInstanceOf(NullClient::class, 'client', $cache);
    }

    public function test_should_create_new_simplecache_instance_with_memcached_client()
    {
        $cache1 = simple_cache_factory('memcached');
        $this->assertAttributeInstanceOf(MemcachedClient::class, 'client', $cache1);

        $cache2 = simple_cache_factory('memcached');
        $this->assertNotSame($cache1, $cache2);
    }
}
