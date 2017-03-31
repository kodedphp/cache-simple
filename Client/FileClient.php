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

use Koded\Caching\Cache;
use Koded\Exceptions\CacheException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class FileClient implements CacheInterface
{

    const E_DIRECTORY_NOT_CREATED = 1;

    private $dir = '';
    private $logger;

    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->initialize($config);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $filename = $this->filename($key, false);

        if (!is_file($filename)) {
            return $default;
        }

        /** @noinspection PhpIncludeInspection */
        $content = include($filename);

        if ($this->expired($content)) {
            $this->delete($key);
            return $default;
        }

        return $content['value'] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return (bool)file_put_contents($this->filename($key), $this->transform($key, $value, $ttl));
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return unlink($this->filename($key, false));
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir,
            \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            ($path->isDir() and !$path->isLink()) ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->get($key, $default);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.

        $items = array_filter($values, function($value, $key) use ($ttl) {
            return $this->set($key, $value, $ttl);
        }, ARRAY_FILTER_USE_BOTH);

        return count($values) === count($items);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        $deleted = array_filter($keys, function($key) {
            return $this->delete($key);
        });

        return count($keys) === count($deleted);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return is_file($this->filename($key, false));
    }

    /**
     * Prepares the cache directory.
     *
     * @param array $config
     * @throws FileClientCacheException
     */
    private function initialize(array $config = [])
    {
        // overrule shell misconfiguration or the web server
        umask(umask() | 0002);
        $dir = rtrim($config['dir'] ?? sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($dir) and false === mkdir($dir, 0775, true)) {
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
     * @return string
     */
    private function filename(string $key, bool $create = true): string
    {
        $filename = sha1($key);
        $dir = $this->dir . substr($filename, 0, 2);

        if ($create and !is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = $dir . DIRECTORY_SEPARATOR . substr($filename, 2) . '.php';

        if ($create) {
            chmod($filename, 0664);
        }

        return $filename;
    }

    /**
     * Creates a cacheable content.
     *
     * @param string $key The cache key
     * @param mixed  $value The value to be cached
     * @param int    $ttl Time to live
     * @return string
     */
    private function transform(string $key, $value, $ttl): string
    {
        $cache = ['<?php'];
        $cache[] = 'return ' . var_export([
                'timestamp' => $ttl,
                'value' => $value,
                'key' => $key,
            ], true);
        $cache[] = ';';

        return join(PHP_EOL, $cache);
    }

    /**
     * Checks the expiration timestamp in the cache.
     *
     * @param array $cache The cached content
     * @return bool
     */
    private function expired(array $cache): bool
    {
        return $cache['timestamp'] ?? time() > $cache['timestamp'];
    }
}

class FileClientCacheException extends CacheException
{
}