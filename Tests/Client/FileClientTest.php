<?php

namespace Koded\Caching\Client;

use Koded\Caching\CacheException;
use Koded\Stdlib\Arguments;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory, vfsStreamWrapper};
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
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Failed to create a cache directory "' . $dir . '/"');

        vfsStreamWrapper::getRoot()->chmod(0400);
        new FileClient(new NullLogger, $dir);
    }

    public function test_cache_content()
    {
        $client = new FileClient(new NullLogger, $this->dir->url());
        $client->set('foo', new Arguments(['foo' => 'bar']));

        $raw = $this->dir->getChild('0/beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33.php')->getContent();
        $this->assertContains(var_export([
            'timestamp' => 32503593600,
            'key' => 'foo',
            'value' => 'O:22:"Koded\\Stdlib\\Arguments":1:{s:10:"' . "\0" . '*' . "\0" . 'storage";a:1:{s:3:"foo";s:3:"bar";}}',
        ], true), $raw);

        $this->assertEquals(new Arguments(['foo' => 'bar']), $client->get('foo'));
    }

    protected function setUp()
    {
        $this->dir = vfsStream::setup();
    }
}
