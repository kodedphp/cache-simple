<?php

namespace Koded\Caching\Client;

use Koded\Caching\CacheException;
use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{

    public function test_should_create_memory_client_without_configuration()
    {
        $client = (new CacheClientFactory(new ConfigFactory))->new();
        $this->assertInstanceOf(MemoryClient::class, $client);
    }

    public function test_should_create_memcached_client()
    {
        if (false === extension_loaded('Memcached')) {
            $this->markTestSkipped('Memcached is not installed on this environment.');
        }

        $client = (new CacheClientFactory(new ConfigFactory))->new('memcached');
        $this->assertInstanceOf(MemcachedClient::class, $client);
    }

    /**
     * @depends test_should_create_memcached_client
     */
    public function test_should_create_memcached_client_with_ttl()
    {
        if (false === extension_loaded('Memcached')) {
            $this->markTestSkipped('Memcached is not installed on this environment.');
        }

        $client = (new CacheClientFactory(new ConfigFactory(['ttl' => 120])))->new('memcached');

        $r = new \ReflectionClass($client);
        $ttl = $r->getProperty('ttl');
        $ttl->setAccessible(true);

        $this->assertSame(120, $ttl->getValue($client));
    }

    public function test_should_create_redis_client()
    {
        if (false === extension_loaded('redis')) {
            $this->markTestSkipped('Redis is not installed on this environment.');
        }

        $client = (new CacheClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),

            'auth' => 'fubar',
            'binary' => 'msgpack'
        ])))->new('redis');

        $this->assertInstanceOf(RedisClient::class, $client);
        $this->assertTrue($client->client()->isConnected());
    }

    public function test_should_create_predis_client()
    {
        $client = (new CacheClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),
        ])))->new('predis');

        $this->assertInstanceOf(PredisClient::class, $client);
        $this->assertTrue($client->client()->isConnected());
    }

    public function test_should_create_file_client()
    {
        $client = (new CacheClientFactory(new ConfigFactory))->new('file');
        $this->assertInstanceOf(FileClient::class, $client);
    }

    public function test_should_create_memory_client()
    {
        $client = (new CacheClientFactory(new ConfigFactory))->new('memory');
        $this->assertInstanceOf(MemoryClient::class, $client);
    }

    public function test_non_supported_logger_exception()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('The cache logger should be NULL or an instance of Psr\Log\LoggerInterface, given Closure');

        (new CacheClientFactory(new ConfigFactory([
            'logger' => function() { }
        ])))->new('file');
    }
}
