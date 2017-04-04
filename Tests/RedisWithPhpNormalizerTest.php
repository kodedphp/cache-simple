<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class RedisWithPhpNormalizerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    protected function setUp()
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        $this->cache = new SimpleCache((new ClientFactory(new ConfigFactory([
            'normalizer' => 'php',
            'host' => getenv('REDIS_SERVER_HOST'),
        ])))->build());
    }
}
