<?php

namespace Koded\Caching;

use DateInterval;
use Koded\Caching\Client\NullClient;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class FunctionsTest extends TestCase
{

    public function test_should_create_simple_cache_with_null_client()
    {
        $this->assertInstanceOf(CacheInterface::class, cache());
    }

    public function test_should_guard_a_proper_cache_key()
    {
        $this->assertNull(guard_cache_key('Proper-Key:1'));
    }

    public function test_should_reject_invalid_cache_key()
    {
        $invalidKey = 'w#4T |5 tH1~';
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage(sprintf('The cache key is invalid, "%s" given', $invalidKey));
        guard_cache_key($invalidKey);
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
        $interval = new DateInterval('PT42S');
        $this->assertSame(42, cache_ttl($interval));
    }

    public function test_should_support_timestamps_before_unix_epoch_on_32bit_systems()
    {
        $interval = DateInterval::createFromDateString('-2000 years');
        $this->assertLessThan(-63100000000, cache_ttl($interval));
    }

    public function test_default_options_creates_null_client_instance()
    {
        $cache = simple_cache_factory();
        $this->assertInstanceOf(NullClient::class, $cache);
    }

    public function test_should_always_create_new_client_instances()
    {
        $cache1 = simple_cache_factory('memcached');
        $cache2 = simple_cache_factory('memcached');

        $this->assertInstanceOf(\Memcached::class, $cache1->client());

        $this->assertNotSame($cache1, $cache2);
        $this->assertNotSame($cache1->client(), $cache2->client());
    }
}
