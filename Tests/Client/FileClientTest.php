<?php

namespace Koded\Caching\Client;

use Koded\Caching\Configuration\FileConfiguration;
use org\bovigo\vfs\{
    vfsStream, vfsStreamDirectory, vfsStreamWrapper
};
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class FileClientTest extends TestCase
{

    /**
     * @var vfsStreamDirectory
     */
    private $dir;

    public function test_nonwritable_cache_directory()
    {
        $dir = $this->dir->url() . '/fubar';
        $this->expectException(FileCacheClientException::class);
        $this->expectExceptionMessage('Failed to create a cache directory "' . $dir . '/"');

        vfsStreamWrapper::getRoot()->chmod(0400);
        new FileClient(new FileConfiguration(['dir' => $dir]), new NullLogger);
    }

    public function test_cache_content()
    {
        $client = new FileClient(new FileConfiguration(['dir' => $this->dir->url()]), new NullLogger);
        $client->set('foo', 'lorem ipsum');

        $raw = $this->dir->getChild('0b/eec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33.php')->getContent();
        $this->assertContains(var_export([
            'timestamp' => 32503593600,
            'key' => 'foo',
            'value' => 'lorem ipsum',
        ], true), $raw);
    }

    protected function setUp()
    {
        $this->dir = vfsStream::setup();
    }
}
