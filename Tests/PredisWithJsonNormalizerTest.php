<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class PredisWithJsonNormalizerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_return_predis_client_instance()
    {
        $this->assertInstanceOf(Client::class, $this->cache->client());
    }

    protected function setUp()
    {
        putenv('CACHE_CLIENT=predis');

        $this->cache = new SimpleCache((new ClientFactory(new ConfigFactory([
            'normalizer' => 'json',

            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),
            'options' => [
                'prefix' => 'test:'
            ],
        ])))->build());
    }
}
