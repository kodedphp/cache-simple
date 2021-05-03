<?php
/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 */

namespace Koded\Caching\Client;

use Koded\Caching\{Cache, CacheException};
use function Koded\Caching\verify_key;

/**
 * @property ShmopClient client
 *
 */
final class ShmopClient implements Cache
{
    use ClientTrait, MultiplesTrait;

    private string $dir;

    public function __construct(string $dir, ?int $ttl)
    {
        $this->dir = $dir;
        $this->ttl = $ttl;
        $this->setDirectory($dir);
    }

    public function get($key, $default = null)
    {
        if (false === $this->has($key, $filename)) {
            return $default;
        }
        $resource = \shmop_open(\fileinode($filename), 'a', 0, 0);
        return \unserialize(\shmop_read($resource, 0, \shmop_size($resource)));
    }

    public function set($key, $value, $ttl = null)
    {
        verify_key($key);
        if (1 > $expiration = $this->timestampWithGlobalTtl($ttl, Cache::DATE_FAR_FAR_AWAY)) {
            // The item is considered expired and must be deleted
            return $this->delete($key);
        }
        $value = \serialize($value);
        $size = \strlen($value);
        $filename = $this->filename($key, true);
        if (false === $resource = @\shmop_open(\fileinode($filename), 'n', 0666, $size)) {
            $resource = \shmop_open(\fileinode($filename), 'w', 0666, $size);
        }
        return \shmop_write($resource, $value, 0) === $size
            && false !== \file_put_contents($filename . '-ttl', $expiration);
    }

    public function delete($key)
    {
        if (false === $this->has($key, $filename)) {
            return true;
        }
        return $this->expire($filename);
    }

    public function clear()
    {
        foreach ((\glob($this->dir . 'shmop-*.cache*') ?: []) as $filename) {
            $this->expire($filename);
        }
        return true;
    }

    public function has($key, &$filename = '')
    {
        verify_key($key);
        $filename = $this->filename($key, false);
        $expiration = (int)(@\file_get_contents($filename . '-ttl') ?: 0);
        if ($expiration <= \time()) {
            $this->expire($filename);
            return false;
        }
        return true;
    }

    private function filename(string $key, bool $create): string
    {
        $filename = $this->dir . 'shmop-' . \sha1($key) . '.cache';
        if ($create) {
            \touch($filename);
            \touch($filename . '-ttl');
            \chmod($filename, 0666);
            \chmod($filename . '-ttl', 0666);
        }
        return $filename;
    }

    /**
     * Prepares the cache directory.
     *
     * @param string $directory
     *
     * @throws CacheException
     */
    private function setDirectory(string $directory): void
    {
        // Overrule shell misconfiguration or the web server
        \umask(\umask() | 0002);
        $dir = $directory ?: \sys_get_temp_dir();
        $dir = \rtrim($dir, '/') . '/';

        if (false === \is_dir($dir) && false === \mkdir($dir, 0775, true)) {
            throw CacheException::forCreatingDirectory($dir);
        }
        $this->dir = $dir;
    }

    private function expire(string $filename): bool
    {
        if (false === $resource = @\shmop_open(fileinode($filename), 'w', 0, 0)) {
            return false;
        }
        \unlink($filename . '-ttl');
        return \shmop_delete($resource);
    }
}
