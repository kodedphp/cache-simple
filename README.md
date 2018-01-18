Koded - Simple Caching Library
==============================

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg)](https://php.net/)
[![Build Status](https://travis-ci.org/kodedphp/cache-simple.svg?branch=master)](https://travis-ci.org/kodedphp/cache-simple)
[![Coverage Status](https://coveralls.io/repos/github/kodedphp/cache-simple/badge.svg?branch=master)](https://coveralls.io/github/kodedphp/cache-simple?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kodedphp/cache-simple/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kodedphp/cache-simple/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/koded/cache-simple.svg)](https://packagist.org/packages/koded/cache-simple)
[![Software license](https://img.shields.io/badge/License-BSD%203--Clause-blue.svg)](LICENSE)

A PSR-16 simple caching library for PHP 7 using several caching technologies.
It supports JSON caching for Redis.


Requirements
------------

> The library is not tested on any Windows OS and may not work as expected there.

- PHP 7.1 or higher

### Redis

There are two client flavors for Redis by using the

- [Redis extension][2]
- [Predis library][5]

and they are not mutually exclusive.

These clients supports JSON serialization for your cache, useful if you want to handle
the cached data in other languages.

Since there is no Redis native support for JSON serialization,
it's done in userland and that always introduces some overhead. **Be aware that the native
PHP and Igbinary serializers are superior.**

- the `RedisClient` is preferred if you install the Redis extension
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

Koded `SimpleCache` is available from the Packagist using the [Composer][3].

    composer require koded/cache-simple

The factory function always creates a new instance of `SimpleCache` with the desired cache client.

```php
/*
 * Creates a SimpleCache instance
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
$config = new ConfigFactory(['serializer' => 'json', 'prefix' => 'test:']);
$client = (new ClientFactory($config))->build('redis');

$cache = new SimpleCache($client, 3600);
```

Configuration directives
------------------------

Current available configuration classes

- [FileConfiguration](#fileconfiguration)
- [MemcachedConfiguration](#memcachedconfiguration)
- [RedisConfiguration](#redisconfiguration)
- [PredisConfiguration](#predisconfiguration)


### MemcachedConfiguration

| Memcached arguments         | Type     | Required | Description |
|:----------------------------|:---------|----------|:------------|
| id                          | string   | no       | Memcached persistent_id value |
| servers                     | array    | no       | A list of nested array with [server, port] values |
| options                     | array    | no       | A set with Memcached options |

The following options are set **by default** when instance of `MemcachedConfiguration` is created,
except for `OPT_PREFIX_KEY` which is there as a reminder that it may be set

| Memcached option            | Default value                  |
|:----------------------------|:-------------------------------|
| OPT_DISTRIBUTION            | DISTRIBUTION_CONSISTENT        |
| OPT_SERVER_FAILURE_LIMIT    | 2                              |
| OPT_REMOVE_FAILED_SERVERS   | true                           |
| OPT_RETRY_TIMEOUT           | 1                              |
| OPT_LIBKETAMA_COMPATIBLE    | true                           |
| OPT_PREFIX_KEY              | null                           |

> Options with value `NULL` will be removed.

There are many [Memcached options][4] that will suit the specific needs for your caching scenarios
and this is something you need to figure out yourself.

Examples:

```php
[
    // Memcached client `persistent_id`
    'id'      => 'items',

    // your Memcached servers list
    'servers' => [
        ['127.0.0.1', 11211],
        ['127.0.0.2', 11211],
        ['127.0.0.2', 11212],
    ],

    // Memcached client options
    'options' => [
        \Memcached::OPT_PREFIX_KEY            => 'i:',  // item prefix
        \Memcached::OPT_REMOVE_FAILED_SERVERS => false, // change the default value
        \Memcached::OPT_DISTRIBUTION          => null   // remove this directive
    ]
]
```


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

- php (default)
- json

> The special config directive is `binary(bool)` for setting the internal serializer
functions to either PHP native un/serialize() or igbinary_un/serialize().

```php
$cache = simple_cache_factory('redis', [
    'serializer' => 'json|php',
    'binary' => true
]);

// otherwise
```
The `binary` directive is effective if `igbinary` extension is installed and loaded.
Otherwise it defaults to PHP un/serialize() functions.

> If you want to change the binary flag on already cached data, you must invalidate
all corresponding cached items, since they are serialized previously with the other
serializer and are not valid for the next one.


### PredisConfiguration

By default the parameters are:

| Parameter | Value          |
|-----------|----------------|
| scheme    | tcp            |
| host      | 127.0.0.1      |
| port      | 6379           |

without any additional `options`.

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

There are many configuration options for this library. Please refer to [Predis configuration page][6].

### FileConfiguration

This is the slowest cache client, please avoid it for production environments.

If you do not provide the cache directory in the configuration, the PHP
function [sys_get_temp_dir()][8] is used to determine where to store the cached files.

```php
$cache = simple_cache_factory('file', ['dir' => '/tmp']);
```


License
-------

The code is distributed under the terms of [The 3-Clause BSD license](LICENSE).


[1]: https://memcached.org
[2]: https://redis.io
[3]: https://getcomposer.org
[4]: http://php.net/manual/en/memcached.constants.php
[5]: https://github.com/nrk/predis
[6]: https://github.com/nrk/predis#client-configuration
[7]: https://github.com/phpredis/phpredis#connect-open
[8]: http://php.net/sys_get_temp_dir
