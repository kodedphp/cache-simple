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
use Koded\Caching\CacheException;
use Koded\Caching\Configuration\FileConfiguration;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * @property FileClient client
 */
final class FileClient implements CacheInterface
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

    public function __construct(FileConfiguration $config, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->initialize((string)$config->get('dir'));
    }

    public function get($key, $default = null)
    {
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
        if ($ttl < 0 || $ttl === 0) {
            // The item is considered expired and must be deleted
            return $this->delete($key);
        }

        return (bool)file_put_contents($this->filename($key), $this->content($key, $value, $ttl));
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
     * @throws FileCacheClientException
     */
    private function initialize(string $directory)
    {
        // Overrule shell misconfiguration or the web server
        umask(umask() | 0002);
        $dir = $directory ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cache';
        $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (false === is_dir($dir) && false === mkdir($dir, 0775, true)) {
            $e = FileCacheClientException::forCreatingDirectory($dir);
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
        $filename = sha1($key);
        $dir = $this->dir . substr($filename, 0, 1);

        if ($create && false === is_dir($dir)) {
            mkdir($dir, 0775, true) || $this->logger->error('Failed to create cache directory: {dir}', ['dir' => $dir]);
        }

        $filename = $dir . DIRECTORY_SEPARATOR . substr($filename, 1) . '.php';

        if ($create && !is_file($filename)) {
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
    private function content(string $key, $value, $ttl): string
    {
        if (null === $ttl) {
            $ttl = (int)(new DateTime('31st December 2999'))->format('U');
        } else {
            $ttl += time();
        }

        return '<?php return ' . var_export([
                'timestamp' => $ttl,
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

/**
 * Class FileCacheClientException
 *
 */
class FileCacheClientException extends CacheException
{
}