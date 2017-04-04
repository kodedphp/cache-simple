<?php

namespace Koded\Caching;

use Koded\Caching\Client\FileClient;
use Koded\Caching\Configuration\FileConfiguration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class FileTest extends TestCase
{

    use SimpleCacheTestCaseTrait;

    public function test_should_create_file_client_without_configuration()
    {
        $client = new FileClient(new FileConfiguration([]), new NullLogger);
        $this->assertAttributeEquals(sys_get_temp_dir() . DIRECTORY_SEPARATOR, 'dir', $client);
    }

    protected function setUp()
    {
        $dir = vfsStream::setup();
        $this->cache = new SimpleCache(new FileClient(new FileConfiguration(['dir' => $dir->url()]), new NullLogger));
    }
}