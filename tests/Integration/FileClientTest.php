<?php

namespace Tests\Koded\Caching\Integration;

use Cache\IntegrationTests\SimpleCacheTest;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

/**
 * @group integration
 */
class FileClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    private vfsStreamDirectory $dir;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        return simple_cache_factory('file');
    }

    protected function setUp(): void
    {
        $this->dir = vfsStream::setup();

        parent::setUp();

        $this->loadGlobalSkippedTests();
    }
}
