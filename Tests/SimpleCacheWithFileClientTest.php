<?php

namespace Koded\Caching;

use Koded\Caching\Client\FileClient;
use org\bovigo\vfs\vfsStream;
use Psr\Log\NullLogger;

class SimpleCacheWithFileClientTest extends SimpleCacheTestCase
{

    public function testShouldCreateFileClientWithoutConfiguration()
    {
        $this->assertAttributeEquals(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'dir', new FileClient([], new NullLogger));
    }

    protected function setUp()
    {
        $dir = vfsStream::setup();
        $this->cache = new SimpleCache(new FileClient(['dir' => $dir->url()], new NullLogger));
    }
}
