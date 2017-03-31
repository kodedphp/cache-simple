<?php

namespace Koded\Caching\Client;

use org\bovigo\vfs\{ vfsStream, vfsStreamDirectory, vfsStreamWrapper };
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class FileClientTest extends TestCase
{

    /**
     * @var vfsStreamDirectory
     */
    private $dir;

    public function testUnwritableCacheDirectory()
    {
        $dir = $this->dir->url() . '/fubar';
        $this->expectException(FileClientCacheException::class);
        $this->expectExceptionMessage('Failed to create a cache directory "' . $dir . '/"');

        vfsStreamWrapper::getRoot()->chmod(0400);
        new FileClient(['dir' => $dir], new NullLogger);
    }

    public function testCacheContent()
    {
        $client = new FileClient(['dir' => $this->dir->url()], new NullLogger);
        $client->set('foo', 'lorem ipsum');

        $raw = $this->dir->getChild('0b/eec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33.php')->getContent();
        $this->assertContains(var_export([
            'timestamp' => null,
            'value' => 'lorem ipsum',
            'key' => 'foo'
        ], true), $raw);
    }

    protected function setUp()
    {
        $this->dir = vfsStream::setup();
    }
}
