<?php

namespace Koded\Caching;

use Koded\Caching\Client\MemcachedClient;
use Koded\Caching\Configuration\ConfigFactory;
use Memcached;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class MemcachedTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_memcached_client()
    {
        $this->assertInstanceOf(Memcached::class, $this->cache->client());
    }

    public function test_should_return_memcached_instance()
    {
        $this->assertInstanceOf(MemcachedClient::class, $this->cache->instance());
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

        $this->cache = new SimpleCache((new ClientFactory(new ConfigFactory()))->build());
    }
}