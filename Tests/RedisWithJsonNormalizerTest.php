<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use function Koded\Stdlib\dump;
use PHPUnit\Framework\TestCase;

class RedisWithJsonNormalizerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    protected function setUp()
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        $this->cache = new SimpleCache((new ClientFactory(new ConfigFactory([
            'normalizer' => 'json',
            'host' => getenv('REDIS_SERVER_HOST'),
        ])))->build());
    }
}
