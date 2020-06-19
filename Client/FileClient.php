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
use Psr\Log\LoggerInterface;
use function Koded\Caching\verify_key;
use function Koded\Stdlib\rmdir;

/**
 * @property FileClient client
 *
 */
final class FileClient implements Cache
{
    use ClientTrait, MultiplesTrait;

    /** @var string */
    private $dir = '';

    /** @var LoggerInterface */
    private $logger;


    public function __construct(LoggerInterface $logger, string $dir, int $ttl = null)
    {
        $this->ttl = $ttl;
        $this->logger = $logger;
        $this->setDirectory($dir);
    }


    public function get($key, $default = null)
    {
        try {
            if ($this->has($key, $filename, $cache)) {
                return unserialize($cache['value']);
            }

            return $default;

        } finally {
            unset($filename, $cache);
        }
    }


    public function set($key, $value, $ttl = null)
    {
        verify_key($key);

        if (1 > $expiration = $this->timestampWithGlobalTtl($ttl, Cache::DATE_FAR_FAR_AWAY)) {
            // The item is considered expired and must be deleted
            return $this->delete($key);
        }

        $filename = $this->filename($key, true);

        return (bool)file_put_contents($filename, $this->data($key, $value, $expiration));
    }


    public function delete($key)
    {
        if (false === $this->has($key, $filename)) {
            return true;
        }

        return unlink($filename);
    }


    public function clear()
    {
        return rmdir($this->dir);
    }


    public function has($key, &$filename = '', &$cache = null)
    {
        verify_key($key);
        $filename = $this->filename($key, false);

        if (false === is_file($filename)) {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        $cache = include $filename;

        if ($cache['timestamp'] <= time()) {
            unlink($filename);

            return false;
        }

        return true;
    }

    /**
     * Normalizes the cache filename.
     *
     * @param string $key    The cache key
     * @param bool   $create Flag to create the file or not
     *
     * @return string
     */
    private function filename(string $key, bool $create): string
    {
        $filename = sha1($key);
        $dir = $this->dir . $filename[0];

        if ($create && false === is_dir($dir)) {
            mkdir($dir, 0775, true)
            || $this->logger->error('Failed to create cache directory in: {dir}', ['dir' => $dir]);
        }

        $filename = $dir . '/' . substr($filename, 1) . '.php';

        if ($create && false === is_file($filename)) {
            touch($filename);
            chmod($filename, 0666);
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
     * Creates a cache content.
     *
     * @param string   $key   The cache key
     * @param mixed    $value The value to be cached
     * @param int|null $ttl   Time to live
     *
     * @return string
     */
    private function data(string $key, $value, int $ttl): string
    {
        return '<?php return ' . var_export([
                'timestamp' => $ttl,
                'key' => $key,
                'value' => serialize($value),
            ], true) . ';';
    }
}
