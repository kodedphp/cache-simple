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

use DateTime;
use FilesystemIterator;
use Koded\Caching\Cache;
use Koded\Caching\CacheException;
use Koded\Caching\Configuration\FileConfiguration;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class FileClient implements CacheInterface
{

    use KeyTrait, ClientTrait;

    const E_DIRECTORY_NOT_CREATED = 1;

    /**
     * @var string
     */
    private $dir = '';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(FileConfiguration $config, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->keyRegex = $config->get('keyRegex', $this->keyRegex);
        $this->initialize((string)$config->dir);
    }

    public function get($key, $default = null)
    {
        $filename = $this->filename($key, false);

        if (!is_file($filename)) {
            return $default;
        }

        /** @noinspection PhpIncludeInspection */
        $content = include $filename;

        if ($this->expired($content)) {
            $this->delete($key);
            return $default;
        }

        return $content['value'] ?? $default;
    }

    public function set($key, $value, $ttl = null)
    {
        if ($ttl < 0 || $ttl === 0) {
            // The item is considered expired and must be deleted
            return $this->delete($key);
        }

        return (bool)file_put_contents($this->filename($key), $this->data($key, $value, $ttl));
    }

    public function delete($key)
    {
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
                ($path->isDir() and !$path->isLink()) ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }

            return true;
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
        // @codeCoverageIgnoreEnd
    }

    public function getMultiple($keys, $default = null)
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->get($key, $default);
        }

        return $items;
    }

    public function setMultiple($values, $ttl = null)
    {
        if ($ttl < 0 || $ttl === 0) {
            // All items are considered expired and must be deleted
            return $this->deleteMultiple(array_keys($values));
        }

        $cached = 0;
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl) && ++$cached;
        }

        return count($values) === $cached;
    }

    public function deleteMultiple($keys)
    {
        $deleted = 0;
        foreach ($keys as $key) {
            $this->delete($key) && ++$deleted;
        }

        return count($keys) === $deleted;
    }

    public function has($key)
    {
        return is_file($this->filename($key, false));
    }

    /**
     * Prepares the cache directory.
     *
     * @param string $directory
     *
     * @throws FileClientCacheException
     */
    protected function initialize(string $directory)
    {
        // overrule shell misconfiguration or the web server
        umask(umask() | 0002);
        $dir = $directory ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cache';
        $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($dir) && false === mkdir($dir, 0775, true)) {
            $e = new FileClientCacheException(Cache::E_DIRECTORY_NOT_CREATED, [':dir' => $dir]);
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
    protected function filename(string $key, bool $create = true): string
    {
        $filename = sha1($key);
        $dir = $this->dir . substr($filename, 0, 2);

        if ($create && !is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = $dir . DIRECTORY_SEPARATOR . substr($filename, 2) . '.php';

        if ($create && !is_file($filename)) {
            touch($filename);
            chmod($filename, 0666);
        }

        return $filename;
    }

    /**
     * Creates a cache content.
     *
     * @param string $key   The cache key
     * @param mixed  $value The value to be cached
     * @param int|null    $ttl   Time to live
     *
     * @return string
     */
    protected function data(string $key, $value, $ttl): string
    {
        if (null === $ttl) {
            $ttl = (new DateTime('31st December 2999'))->getTimestamp();
        } else {
            $ttl += time();
        }

        $cache = ['<?php'];
        $cache[] = 'return ' . var_export([
                'timestamp' => $ttl,
                'key' => $key,
                'value' => $value,
            ], true);
        $cache[] = ';';

        return join(PHP_EOL, $cache);
    }

    /**
     * Checks the expiration timestamp in the cache.
     *
     * @param array $cache The cached content
     *
     * @return bool
     */
    protected function expired(array $cache): bool
    {
        return time() >= $cache['timestamp'];
    }
}

/**
 * Class FileClientCacheException
 *
 */
class FileClientCacheException extends CacheException
{
}