<?php

namespace Koded\Caching\Configuration;

use function Koded\Stdlib\dump;
use Memcached;
use PHPUnit\Framework\TestCase;

class MemcachedConfigurationTest extends TestCase
{

    public function test_()
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
    }
}
