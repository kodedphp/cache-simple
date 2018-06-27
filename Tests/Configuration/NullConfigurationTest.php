<?php

namespace Koded\Caching\Configuration;

use Koded\Caching\Client\NullClient;
use Koded\Caching\ClientFactory;
use PHPUnit\Framework\TestCase;

class NullConfigurationTest extends TestCase
{

    public function test_that_env_with_null_value_creates_correct_instance()
    {
        $client = (new ClientFactory(new ConfigFactory))->build();
        $this->assertInstanceOf(NullClient::class, $client);
    }

    protected function setUp()
    {
        putenv('CACHE_CLIENT=null');
    }

    protected function tearDown()
    {
        putenv('CACHE_CLIENT=');
    }
}
