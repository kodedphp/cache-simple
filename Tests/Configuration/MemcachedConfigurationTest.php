<?php

namespace Koded\Caching\Configuration;

use Memcached;
use PHPUnit\Framework\TestCase;

class MemcachedConfigurationTest extends TestCase
{

    public function test_with_servers_array_and_removing_some_options()
    {
        $config = new MemcachedConfiguration([
            'id' => 'test',
            'servers' => [
                ['memcached123', 11211]
            ],
            'options' => [
                Memcached::OPT_REMOVE_FAILED_SERVERS => false,
                Memcached::OPT_RETRY_TIMEOUT => 5,
                Memcached::OPT_PREFIX_KEY => 'test-prefix',

                // mark for removal
                Memcached::OPT_DISTRIBUTION => null,
                Memcached::OPT_SERVER_FAILURE_LIMIT => null,
            ]
        ]);

        $expected = $config->getOptions();

        $this->assertFalse($expected[Memcached::OPT_REMOVE_FAILED_SERVERS]);
        $this->assertSame('test-prefix', $expected[Memcached::OPT_PREFIX_KEY]);
        $this->assertSame(5, $expected[Memcached::OPT_RETRY_TIMEOUT]);

        // these 2 should be removed
        $this->assertArrayNotHasKey(Memcached::OPT_DISTRIBUTION, $expected);
        $this->assertArrayNotHasKey(Memcached::OPT_SERVER_FAILURE_LIMIT, $expected);

        $this->assertSame([['memcached123', 11211]], $config->getServers());
    }

    public function test_with_env_variable()
    {
        putenv('MEMCACHED_POOL=[["mem", 11212]]');
        $config = new MemcachedConfiguration;

        $this->assertNull($config->id);
        $this->assertSame([], $config->servers);
        $this->assertSame([["mem", 11212]], $config->getServers());
    }

    public function test_should_build_default_arguments()
    {
        $config = new MemcachedConfiguration;

        $this->assertNull($config->id);
        $this->assertSame([], $config->servers);
        $this->assertSame([['127.0.0.1', 11211]], $config->getServers());

        $this->assertSame([
            Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
            Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
            Memcached::OPT_REMOVE_FAILED_SERVERS => true,
            Memcached::OPT_RETRY_TIMEOUT => 1,
            Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
            // OPT_PREFIX_KEY is filtered out

        ], $config->getOptions());
    }

    protected function tearDown()
    {
        putenv('MEMCACHED_POOL=');
    }
}