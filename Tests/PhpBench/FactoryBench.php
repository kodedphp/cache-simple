<?php

namespace Tests\Koded\Caching\PhpBench;

use function Koded\Caching\simple_cache_factory;

/**
 * @Iterations(3)
 * @Groups({"factory"})
 */
final class FactoryBench
{
    public function __construct()
    {
        if (getenv('CI')) {
            putenv('MEMCACHED_POOL=[["127.0.0.1", 11211]]');
            putenv('REDIS_SERVER_HOST=127.0.0.1');
        } else {
            putenv('MEMCACHED_POOL=[["memcached", 11211]]');
            putenv('REDIS_SERVER_HOST=redis');
        }
    }

    public function bench_memcached()
    {
        simple_cache_factory('memcached');
    }

    public function bench_redis()
    {
        simple_cache_factory('redis', ['host' => getenv('REDIS_SERVER_HOST')]);
    }

    public function bench_predis()
    {
        simple_cache_factory('predis', ['host' => getenv('REDIS_SERVER_HOST')]);
    }

    public function bench_shmop()
    {
        simple_cache_factory('shmop', ['dir' => __DIR__ . '/../../build']);
    }

    public function bench_file()
    {
        simple_cache_factory('file', ['dir' => __DIR__ . '/../../build']);
    }

    public function bench_memory()
    {
        simple_cache_factory('memory');
    }

    public function bench_default()
    {
        simple_cache_factory();
    }
}
