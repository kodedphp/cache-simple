<?php

namespace Tests\Koded\Caching\PhpBench;

use Koded\Caching\Cache;
use function Koded\Caching\simple_cache_factory;
use function Koded\Stdlib\rmdir as clear_directory;

/**
 * @Groups({"read-write"})
 * @Iterations(3)
 */
final class ReadWriteBench
{
    /** @var Cache */
    private $memcached;
    private $redis;
    private $predis;
    private $file;
    private $shmop;
    private $memory;

    private $dir = '';
    private $key = '';
    private $data;

    public function __construct()
    {
        $this->dir = sys_get_temp_dir() . '/koded/benchmarks';
        $this->clearDirectory();
        mkdir($this->dir, 0777, true);

        if (getenv('CI')) {
            putenv('MEMCACHED_POOL=[["127.0.0.1", 11211]]');
            putenv('REDIS_SERVER_HOST=127.0.0.1');
        } else {
            putenv('MEMCACHED_POOL=[["memcached", 11211]]');
            putenv('REDIS_SERVER_HOST=redis');
        }

        $this->memcached = simple_cache_factory('memcached');
        $this->redis = simple_cache_factory('redis', ['host' => getenv('REDIS_SERVER_HOST'), 'db' => 1]);
        $this->predis = simple_cache_factory('predis', ['host' => getenv('REDIS_SERVER_HOST'), 'db' => 2]);
        $this->file = simple_cache_factory('file', ['dir' => $this->dir]);
        $this->shmop = simple_cache_factory('shmop', ['dir' => $this->dir]);
        $this->memory = simple_cache_factory();

        $this->key = 'test.' . sha1(time());
        $this->data = file_get_contents(__DIR__ . '/../../composer.json');
    }

    public function __destruct()
    {
       $this->clearDirectory();
    }

    public function bench_memcached()
    {
        assert($this->memcached->set($this->key, $this->data), __FUNCTION__);

        assert(null !== $this->memcached->get($this->key), __FUNCTION__);
        assert($this->memcached->delete($this->key), __FUNCTION__);
    }

    public function bench_redis()
    {
        assert($this->redis->set($this->key, $this->data), __FUNCTION__);

        assert(null !== $this->redis->get($this->key), __FUNCTION__);
        assert($this->memcached->delete($this->key), __FUNCTION__);
    }

    public function bench_predis()
    {
        assert($this->predis->set($this->key, $this->data), __FUNCTION__);

        assert(null !== $this->predis->get($this->key), __FUNCTION__);
        assert($this->predis->delete($this->key), __FUNCTION__);
    }

    public function bench_file()
    {
        assert($this->file->set($this->key, $this->data), __FUNCTION__);

        assert(null !== $this->file->get($this->key), __FUNCTION__);
        assert($this->file->delete($this->key), __FUNCTION__);
    }

    public function bench_shmop()
    {
        assert($this->shmop->set($this->key, $this->data), __FUNCTION__);

        assert(null !== $this->shmop->get($this->key), __FUNCTION__);
        assert($this->shmop->delete($this->key), __FUNCTION__);
    }

    public function bench_memory()
    {
        assert($this->memory->set($this->key, $this->data), __FUNCTION__);

        assert(null !== $this->memory->get($this->key), __FUNCTION__);
        assert($this->memory->delete($this->key), __FUNCTION__);
    }

    private function clearDirectory()
    {
        if (is_dir($this->dir)) {
            clear_directory($this->dir);
            \rmdir($this->dir);
        }
    }
}
