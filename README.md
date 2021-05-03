Koded - Simple Caching Library
==============================

[![Latest Stable Version](https://img.shields.io/packagist/v/koded/cache-simple.svg)](https://packagist.org/packages/koded/cache-simple)
[![Build Status](https://travis-ci.org/kodedphp/cache-simple.svg?branch=master)](https://travis-ci.org/kodedphp/cache-simple)
[![Code Coverage](https://scrutinizer-ci.com/g/kodedphp/cache-simple/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kodedphp/cache-simple/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kodedphp/cache-simple/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kodedphp/cache-simple/?branch=master)
[![Packagist Downloads](https://img.shields.io/packagist/dt/koded/cache-simple.svg)](https://packagist.org/packages/koded/cache-simple)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg)](https://php.net/)

A [PSR-16][10] caching library for PHP7 using several caching technologies.
It supports JSON caching for Redis.


Requirements
------------

> The library is not tested on any Windows OS and may not work as expected there.

The recommended installation method is via [Composer][3]
```shell script
composer require koded/cache-simple
```

### Redis

There are two client flavors for Redis by using the

  - [Redis extension][2]
  - [Predis library][5]

and they are not mutually exclusive.

These clients supports JSON serialization for the cache, useful for handling
the cached data in other programming languages.

Since there is no Redis native support for JSON serialization,
it's done in userland and that always introduces some overhead. **Be aware that the native
PHP and Igbinary functions are superior.**

  - the `RedisClient` is preferred if Redis extension is installed
  - the `PredisClient` can be used otherwise

```php
// with Redis extension
simple_cache_factory('redis');

// with Predis library
simple_cache_factory('predis');
```

### Memcached

Please install the [Memcached extension][1].

Usage
-----

The factory function always creates a new instance of specific 
`SimpleCacheInterface` client implementation.

```php
/*
 * Creates a simple cache instance
 * with MemcachedClient and default configuration
 */

$cache = simple_cache_factory('memcached');
```

```php
/*
 * Some configuration directives for the cache client
 * are passed in the second argument as array
 */

$cache = simple_cache_factory('redis', [
    'host'       => '127.0.0.1',
    'serializer' => 'json',
    'prefix'     => 'test:',
    'ttl'        => 3600 // 1 hour global TTL
]);
```

A bit verbose construction for the same instance is

```php
$config = new ConfigFactory(['serializer' => 'json', 'prefix' => 'test:', 'ttl' => 3000]);
$cache = (new ClientFactory($config))->new('redis');
```

Configuration directives
------------------------

Current available configuration classes

  - [RedisConfiguration](#redisconfiguration)
  - [MemcachedConfiguration](#memcachedconfiguration)
  - [FileConfiguration](#fileconfiguration)
  - [PredisConfiguration](#predisconfiguration)



### RedisConfiguration

Please refer to [Redis extension connect][7] method.

| Parameter | Value          |
|-----------|----------------|
| host      | 127.0.0.1      |
| port      | 6379           |
| timeout   | 0.0            |
| reserved  | null           |
| retry     | 0              |

```php
// Without defining the parameters the above directives are used as default
$cache = simple_cache_factory('redis');
```

#### Serializers

  - `php` (default)
  - `json`

> The special config directive is `binary(string)` for setting the internal serializer
  functions to either PHP native `un/serialize()`, `igbinary_un/serialize()` or `msgpack_un/pack()`.

```php
$cache = simple_cache_factory('redis', [
    'binary' => \Koded\Stdlib\Serializer::MSGPACK
]);
```

The `binary` directive is effective if `igbinary` and/or `msgpack` extensions are installed and loaded.
Otherwise it defaults to PHP `un/serialize()` functions.

> You can change the binary flag on already cached data, but you should invalidate the
  previously cached items, since they are already serialized and stored in the cache.

##### JSON serializer options

The **default** options for [json_encode()][9] function are:
  - JSON_PRESERVE_ZERO_FRACTION
  - JSON_UNESCAPED_SLASHES
  - JSON_THROW_ON_ERROR

To set the desired options, use the `options` configuration directive:

```php
$cache = simple_cache_factory('redis', [
    'serializer' => 'json',
    'options' => JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT
]);
```
JSON options are applied with bitmask operators. The above example will
- remove `JSON_UNESCAPED_SLASHES` (because it's already set)
- add `JSON_FORCE_OBJECT`

### MemcachedConfiguration

| Memcached arguments         | Type     | Required | Description |
|:----------------------------|:---------|----------|:------------|
| id                          | string   | no       | Memcached persistent_id value |
| servers                     | array    | no       | A list of nested array with \[server, port\] values |
| options                     | array    | no       | A list of Memcached options |
| ttl                         | int      | no       | Global TTL (in seconds) |

The following options are set **by default** when an instance of `MemcachedConfiguration` is created,
except for `OPT_PREFIX_KEY` which is there as a reminder that it may be set.

| Memcached option            | Default value                  |
|:----------------------------|:-------------------------------|
| OPT_DISTRIBUTION            | DISTRIBUTION_CONSISTENT        |
| OPT_SERVER_FAILURE_LIMIT    | 2                              |
| OPT_REMOVE_FAILED_SERVERS   | true                           |
| OPT_RETRY_TIMEOUT           | 1                              |
| OPT_LIBKETAMA_COMPATIBLE    | true                           |
| OPT_PREFIX_KEY              | null                           |


> Options with `NULL` value will be removed.

There are many [Memcached options][4] that may suit the specific needs for the caching scenarios
and this is something the developer/s needs to figure it out.

Examples:

```php
[
    // Memcached client `persistent_id`
    'id' => 'items',

    // your Memcached servers list
    'servers' => [
        ['127.0.0.1', 11211],
        ['127.0.0.2', 11211],
        ['127.0.0.2', 11212],
    ],

    // Memcached client options
    'options' => [
        \Memcached::OPT_PREFIX_KEY            => 'i:',  // cache item prefix
        \Memcached::OPT_REMOVE_FAILED_SERVERS => false, // changes the default value
        \Memcached::OPT_DISTRIBUTION          => null   // remove this directive with NULL
    ],

    // the global expiration time (for ALL cached items)
    'ttl' => 120,
]
```


### PredisConfiguration

By default the parameters are:

| Parameter | Value          |
|-----------|----------------|
| scheme    | tcp            |
| host      | 127.0.0.1      |
| port      | 6379           |

Examples:

```php
$cache = simple_cache_factory('predis');
```

```php
$cache = simple_cache_factory('predis', [
    'scheme' => 'unix',
    'path' => '/path/to/redis.sock',
    'options' => [
        'prefix' => 'i:',
        'exceptions' => true,
        'parameters' => [
            'password' => getenv('REDIS_PASSWORD'),
            'database' => 1
        ]
    ]
]);
```

There are many configuration options for this package.
Please refer to [Predis configuration page][6].


### Shared Memory (shmop)

Requires a [PHP shmop extension][11].

```php
$cache = simple_cache_factory('shmop', [
    'dir' => '/path/to/app/cache', // optional
    'ttl' => null,                 // global TTL
]);
```


### FileConfiguration

Please avoid it on production environments, or use it as a last option.

If cache directory is not provided in the configuration, the PHP
function [sys_get_temp_dir()][8] is used to store the cached files
in the OS "temporary" directory.

```php
$cache = simple_cache_factory('file', ['dir' => '/tmp']);
```


### MemoryClient

This client will store the cached items in the memory for the duration of the script's lifetime.
It is useful for development, but not for production.

> `MemoryClient` is also the default client if you do not 
  require a specific client in `cache_simple_factory()`

```php
$cache = simple_cache_factory('memory');
$cache = simple_cache_factory();  // also creates a MemoryClient
```

Code quality
------------

```shell script
vendor/bin/phpunit

vendor/bin/phpbench run --report=default --group=factory
vendor/bin/phpbench run --report=default --group=read-write
```
### Benchmarks

The benchmarks are flaky and dependant on the environment. This table gives 
a non accurate insight how client performs at write-read-delete operations.

To find out what may be the fastest choice for your environment, run
```
vendor/bin/phpbench run --report=default --group=read-write

+----------------+-----------------+-----+------+-----+--------+-------+
| benchmark      | subject         | set | revs | its | rstdev | diff  |
+----------------+-----------------+-----+------+-----+--------+-------+
| ReadWriteBench | bench_memcached | 0   | 1    | 3   | 1.61%  | 4.60x |
| ReadWriteBench | bench_redis     | 0   | 1    | 3   | 1.44%  | 4.64x |
| ReadWriteBench | bench_predis    | 0   | 1    | 3   | 1.25%  | 5.79x |
| ReadWriteBench | bench_file      | 0   | 1    | 3   | 3.28%  | 2.67x |
| ReadWriteBench | bench_shmop     | 0   | 1    | 3   | 3.65%  | 2.94x |
| ReadWriteBench | bench_memory    | 0   | 1    | 3   | 1.41%  | 1.00x |
+----------------+-----------------+-----+------+-----+--------+-------+
```


License
-------
[![Software license](https://img.shields.io/badge/License-BSD%203--Clause-blue.svg)](LICENSE)

The code is distributed under the terms of [The 3-Clause BSD license](LICENSE).


[1]: https://memcached.org
[2]: https://redis.io
[3]: https://getcomposer.org
[4]: http://php.net/manual/en/memcached.constants.php
[5]: https://github.com/nrk/predis
[6]: https://github.com/nrk/predis#client-configuration
[7]: https://github.com/phpredis/phpredis#connect-open
[8]: http://php.net/sys_get_temp_dir
[9]: http://php.net/json_encode
[10]: https://www.php-fig.org/psr/psr-16/
[11]: https://www.php.net/manual/en/book.shmop.php
