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
        $expected = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        $this->assertAttributeEquals($expected, 'dir', $client);
    }

    public function test_should_return_self()
    {
        $this->assertInstanceOf(FileClient::class, $this->cache->client());
    }

    protected function setUp()
    {
        $dir = vfsStream::setup();
        $this->cache = new FileClient(new FileConfiguration(['dir' => $dir->url()]), new NullLogger);
    }
}