<?php

namespace Koded\Caching\Client;

use Koded\Caching\CacheException;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{

    public function test_should_create_null_client_without_configuration()
    {
        $client = (new CacheFactory(new ConfigFactory))->build();
        $this->assertInstanceOf(NullClient::class, $client);
    }

    public function test_should_create_memcached_client()
    {
        if (false === extension_loaded('Memcached')) {
            $this->markTestSkipped('Memcached is not installed on this environment.');
        }

        $client = (new CacheFactory(new ConfigFactory))->build('memcached');
        $this->assertInstanceOf(MemcachedClient::class, $client);
    }

    public function test_should_create_redis_client()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis is not installed on this environment.');
        }

        $client = (new CacheFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'auth' => 'fubar',
            'binary' => 'msgpack'
        ])))->build('redis');

        $this->assertInstanceOf(RedisClient::class, $client);
    }

    public function test_should_create_predis_client()
    {
        $client = (new CacheFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST')
        ])))->build('predis');

        $this->assertInstanceOf(PredisClient::class, $client);
    }

    public function test_should_create_file_client()
    {
        $client = (new CacheFactory(new ConfigFactory))->build('file');
        $this->assertInstanceOf(FileClient::class, $client);
    }

    public function test_should_create_memory_client()
    {
        $client = (new CacheFactory(new ConfigFactory))->build('memory');
        $this->assertInstanceOf(MemoryClient::class, $client);
    }


    public function test_non_supported_logger_exception()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('The cache logger should be NULL or an instance of Psr\Log\LoggerInterface, Closure given');

        (new CacheFactory(new ConfigFactory([
            'logger' => function() {
            }
        ])))->build('file');
    }
}
