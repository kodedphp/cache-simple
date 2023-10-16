<?php

namespace Tests\Koded\Caching\Client;

use Koded\Caching\CacheException;
use Koded\Caching\Client\FileClient;
use Koded\Stdlib\Arguments;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory, vfsStreamWrapper};
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use function Koded\Stdlib\now;

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
        $this->assertStringContainsString(var_export([
            'timestamp' => 32503593600,
            'key' => 'foo',
            'value' => 'O:22:"Koded\\Stdlib\\Arguments":1:{s:7:"' . "\0" . '*' . "\0" . 'data";a:1:{s:3:"foo";s:3:"bar";}}',
        ], true), $raw);

        $this->assertEquals(new Arguments(['foo' => 'bar']), $client->get('foo'));
    }

    public function test_global_ttl()
    {
        $now = now()->getTimestamp();
        $client = new FileClient(new NullLogger, $this->dir->url(), 2);
        $client->set('key', 'value');

        $data = include $this->dir->getChild('a/62f2225bf70bfaccbc7f1ef2a397836717377de.php')->url();
        $this->assertEquals($now + 2, $data['timestamp']);
        $this->assertEquals('value', $client->get('key'));

        sleep(3);
        $this->assertFalse($client->has('key'));
    }

    protected function setUp(): void
    {
        $this->dir = vfsStream::setup();
    }
}
