<?php

namespace Koded\Caching;

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

    public function test_should_extend_the_time()
    {
        $now = time();
        $this->assertSame(100 + $now, cache_ttl(100));
    }

    public function test_should_return_null_if_ttl_is_null()
    {
        $this->assertNull(cache_ttl(null));
    }

    public function test_should_extend_ttl_from_date_interval()
    {
        $interval = new \DateInterval('PT42S');
        $this->assertGreaterThan(time(), cache_ttl($interval));
    }
}
