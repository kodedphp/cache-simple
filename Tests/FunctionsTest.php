<?php

namespace Koded\Caching;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Koded\Caching\Client\MemoryClient;
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{

    public function test_should_guard_a_proper_cache_key()
    {
        $this->assertNull(verify_key('Proper-Key:1'));
    }

    public function test_should_reject_invalid_cache_key()
    {
        $invalidKey = 'w#4T /5 tH1~';
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('The cache key is invalid, given (string) \'w#4T /5 tH1~\'');
        verify_key($invalidKey);
    }

    public function test_should_return_the_same_ttl_if_less_then_one()
    {
        $this->assertSame(0, normalize_ttl(0));
        $this->assertSame(-1, normalize_ttl(-1));
    }

    public function test_should_return_null_if_ttl_is_null()
    {
        $this->assertNull(normalize_ttl(null));
    }

    public function test_should_return_the_same_ttl()
    {
        $this->assertSame(100, normalize_ttl(100));
    }

    public function test_should_set_ttl_from_date_interval()
    {
        $interval = new DateInterval('PT42S');
        $this->assertSame(42, normalize_ttl($interval));
    }

    public function test_should_transform_DateTime_to_integer()
    {
        $this->assertGreaterThanOrEqual(time(), normalize_ttl(new DateTimeImmutable));
        $this->assertGreaterThanOrEqual(time(), normalize_ttl(new DateTime));
    }

    public function test_should_support_timestamps_before_unix_epoch_on_32bit_systems()
    {
        $interval = DateInterval::createFromDateString('-2000 years');
        $this->assertLessThan(-63100000000, normalize_ttl($interval));
    }

    public function test_default_options_creates_memory_client_instance()
    {
        $cache = simple_cache_factory();
        $this->assertInstanceOf(MemoryClient::class, $cache);
    }

    public function test_should_always_create_new_client_instances()
    {
        $cache1 = simple_cache_factory('memory');
        $cache2 = simple_cache_factory('memory');

        $this->assertNotSame($cache1, $cache2);
        $this->assertNotSame($cache1->client(), $cache2->client());
    }
}
