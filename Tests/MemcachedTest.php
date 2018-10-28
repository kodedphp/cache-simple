<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class MemcachedTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_memcached_client()
    {
        $this->assertInstanceOf(\Memcached::class, $this->cache->client());
    }

    protected function setUp()
    {
        putenv('CACHE_CLIENT=memcached');

        if (getenv('CI')) {
            putenv('MEMCACHED_POOL=[["127.0.0.1", 11211]]');
        } else {
            putenv('MEMCACHED_POOL=[["memcached", 11211]]');
        }

        if (false === extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension is not loaded.');
        }

        $this->cache = (new CacheClientFactory(new ConfigFactory))->build();
    }
}