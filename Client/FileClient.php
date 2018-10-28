<?php

/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 *
 */

namespace Koded\Caching\Client;

use FilesystemIterator;
use Koded\Caching\{Cache, CacheException};
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use function Koded\Caching\{verify_key, normalize_ttl};

/**
 * @property FileClient client
 */
final class FileClient implements CacheInterface, Cache
{

    use ClientTrait, MultiplesTrait;

    /**
     * @var string
     */
    private $dir = '';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, string $dir, int $ttl = null)
    {
        $this->ttl = $ttl;
        $this->logger = $logger;
        $this->setDirectory($dir);
    }

    public function get($key, $default = null)
    {
        verify_key($key);
        $filename = $this->filename($key, false);

        if (false === is_file($filename)) {
            return $default;
        }

        /** @noinspection PhpIncludeInspection */
        $content = include $filename;

        if ($this->expired($content)) {
            $this->delete($key);
            return $default;
        }

        return $content['value'] ? unserialize($content['value']) : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        verify_key($key);
        $ttl = normalize_ttl($ttl);

        if ($ttl !== null && $ttl < 1) {
            // The item is considered expired and must be deleted
            return $this->delete($key);
        }

        return (bool)file_put_contents($this->filename($key), $this->data($key, $value, $ttl));
    }

    public function delete($key)
    {
        verify_key($key);
        $filename = $this->filename($key, false);

        if (is_file($filename)) {
            return unlink($filename);
        }

        return true;
    }

    public function clear()
    {
        try {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir,
                FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                ($path->isDir() && !$path->isLink()) ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }

            return true;
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());

            return false;
            // @codeCoverageIgnoreEnd
        }
    }

    public function has($key)
    {
        verify_key($key);

        return is_file($this->filename($key, false));
    }

    /**
     * Prepares the cache directory.
     *
     * @param string $directory
     *
     * @throws CacheException
     */
    private function setDirectory(string $directory)
    {
        // Overrule shell misconfiguration or the web server
        umask(umask() | 0002);
        $dir = $directory ?: sys_get_temp_dir() . '/_cache';
        $dir = rtrim($dir, '/') . '/';

        if (false === is_dir($dir) && false === mkdir($dir, 0775, true)) {
            $e = CacheException::forCreatingDirectory($dir);
            $this->logger->error($e->getMessage());
            throw $e;
        }

        $this->dir = $dir;
    }

    /**
     * Normalizes the cache filename.
     *
     * @param string $key    The cache key
     * @param bool   $create [optional] Flag for dir/file mods
     *
     * @return string
     */
    private function filename(string $key, bool $create = true): string
    {
        verify_key($key);
        $filename = sha1($key);
        $dir = $this->dir . substr($filename, 0, 1);

        if ($create && false === is_dir($dir)) {
            mkdir($dir, 0775, true) || $this->logger->error('Failed to create cache directory in: {dir}', [
                'dir' => $dir
            ]);
        }

        $filename = $dir . '/' . substr($filename, 1) . '.php';

        if ($create && false === is_file($filename)) {
            touch($filename);
            chmod($filename, 0666);
        }

        return $filename;
    }

    /**
     * Creates a cache content.
     *
     * @param string   $key   The cache key
     * @param mixed    $value The value to be cached
     * @param int|null $ttl   Time to live
     *
     * @return string
     */
    private function data(string $key, $value, $ttl): string
    {
        return '<?php return ' . var_export([
                'timestamp' => $this->timestampWithGlobalTtl($ttl, Cache::DATE_FAR_FAR_AWAY),
                'key' => $key,
                'value' => serialize($value),
            ], true) . ';';
    }

    /**
     * Checks the expiration timestamp in the cache.
     *
     * @param array $cache The cached content
     *
     * @return bool
     */
    private function expired(array $cache): bool
    {
        return $cache['timestamp'] <= time();
    }
}
