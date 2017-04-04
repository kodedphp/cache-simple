<?php

namespace Koded\Caching;

use Koded\Caching\Configuration\ConfigFactory;
use PHPUnit\Framework\TestCase;

class PredisWithPhpNormalizerTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    protected function setUp()
    {
        putenv('CACHE_CLIENT=predis');

        $this->cache = new SimpleCache((new ClientFactory(new ConfigFactory([
            'host' => getenv('REDIS_SERVER_HOST'),
            'port' => getenv('REDIS_SERVER_PORT'),
            'options' => [
                'prefix' => 'test:'
            ],
        ])))->build());
    }
}
