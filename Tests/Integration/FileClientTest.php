<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};
use Psr\SimpleCache\CacheInterface;

class FileClientTest extends SimpleCacheTest
{

    use SimpleCacheIntegrationTrait;

    /**
     * @var vfsStreamDirectory
     */
    private $dir;

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
    }
}
