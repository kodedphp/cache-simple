<?php

namespace Koded\Caching;

use Koded\Caching\Client\CacheFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Interfaces\Serializer;
use PHPUnit\Framework\TestCase;

class RedisWithPhpSerializerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    protected function setUp()
    {
        putenv('CACHE_CLIENT=redis');

        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded.');
        }

        $this->cache = (new CacheFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'serializer' => Serializer::PHP
        ])))->build();
    }
}
