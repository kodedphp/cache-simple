<?php

namespace Koded\Caching\Client;

use Koded\Caching\Configuration\ConfigFactory;
use Koded\Caching\SimpleCacheTestCaseTrait;
use PHPUnit\Framework\TestCase;
use function Koded\Caching\simple_cache_factory;
use function Koded\Stdlib\now;

class MemoryClientTest extends TestCase
{
    use SimpleCacheTestCaseTrait;

    public function test_global_ttl_when_null()
    {
        /** @var MemoryClient $cache */
        $cache = simple_cache_factory('memory');

        $this->assertNull($cache->getTtl(), 'Default TTL is NULL');
        $this->assertAttributeEquals([], 'expiration', $cache);

        $cache->set('key', 'value', 10);
        $this->assertGreaterThanOrEqual(10 + now()->getTimestamp(), $cache->getExpirationFor('key'),
            'Exp. time is calculated internally');

        $this->assertNull($cache->getTtl(), 'Global TTL is not changed on explicit item TTL');
    }

    public function test_global_ttl_when_set()
    {
        /** @var MemoryClient $cache */
        $cache = simple_cache_factory('memory', ['ttl' => 60]);

        $this->assertEquals(60, $cache->getTtl());
        $this->assertAttributeEquals([], 'expiration', $cache, 'Ex. time is empty if global TTL is set');

        $cache->set('key', 'value');
        $this->assertGreaterThanOrEqual(60 + now()->getTimestamp(), $cache->getExpirationFor('key'),
            'Global TTL is applied if explicit is NULL');

        $cache->set('key', 'value', 120);
        $this->assertGreaterThanOrEqual(120 + now()->getTimestamp(), $cache->getExpirationFor('key'),
            'Global TTL is ignored if explicit is not NULL');
    }

    protected function setUp(): void
    {
        putenv('CACHE_CLIENT=memory');
        $this->cache = (new ClientFactory(new ConfigFactory))->new();
    }
}
