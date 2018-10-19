<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheFactory;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class PredisWithPhpSerializerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_expired_cache()
    {
        $this->assertTrue($this->cache->set('foo', 'bar', 1));

        // At this point the cached item exists
        $this->assertTrue($this->cache->has('foo'));

        // but after some time it is deleted
        sleep(3);
        $this->assertSame('expired', $this->cache->get('foo', 'expired'));
        $this->assertFalse($this->cache->has('foo'));
    }

    protected function setUp()
    {
        putenv('CACHE_CLIENT=predis');

        $this->cache = (new CacheFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),
            'options' => [
                'prefix' => 'test:'
            ],
        ])))->build();
    }
}