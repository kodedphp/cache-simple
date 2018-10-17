<?php

namespace Koded\Caching\Client;

use Koded\Caching\Configuration\ConfigFactory;
use Koded\Caching\SimpleCacheTestCaseTrait;
use PHPUnit\Framework\TestCase;

class MemoryClientTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    protected function setUp()
    {
        putenv('CACHE_CLIENT=memory');

        $this->cache = (new ClientFactory(new ConfigFactory))->build();
    }
}
