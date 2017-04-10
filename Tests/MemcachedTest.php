<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use Memcached;
use PHPUnit\Framework\TestCase;

class MemcachedTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_memcached_instance()
    {
        $this->assertInstanceOf(Memcached::class, $this->cache->client());
    }

    protected function setUp()
    {
        putenv('CACHE_CLIENT=memcached');

        if (false === extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension is not loaded.');
        }

        $this->cache = new SimpleCache((new ClientFactory(new ConfigFactory()))->build());
    }
}
