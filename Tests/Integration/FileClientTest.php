<?php

namespace Koded\Caching\Tests\Integration;

use Cache\IntegrationTests\SimpleCacheTest;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};
use Psr\SimpleCache\CacheInterface;
use function Koded\Caching\simple_cache_factory;

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

    protected function setUp()
    {
        $this->dir = vfsStream::setup();
        parent::setUp();
    }
}
