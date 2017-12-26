Koded - Simple Caching Library
==============================

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg)](https://php.net/)
[![Build Status](https://travis-ci.org/kodedphp/cache-simple.svg?branch=master)](https://travis-ci.org/kodedphp/cache-simple)
[![Coverage Status](https://coveralls.io/repos/github/kodedphp/cache-simple/badge.svg?branch=master)](https://coveralls.io/github/kodedphp/cache-simple?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kodedphp/cache-simple/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kodedphp/cache-simple/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/koded/cache-simple.svg)](https://packagist.org/packages/koded/cache-simple)
[![Software license](https://img.shields.io/badge/License-BSD%203--Clause-blue.svg)](LICENSE)

A PSR-16 simple caching library for PHP 7 using several caching technologies.


Requirements
------------

Before using the Redis or Memcached clients, an installation of these PHP extensions is required.

- PHP 7.1 or higher
- [Redis extension][1]
- [Memcached extension][2]

> The library is not tested on any Windows OS and may not work as expected there.


Usage
-----

Koded SimpleCache is available from the Packagist using the [Composer][3].
    
    composer require koded/cache-simple
    
The factory function always creates a new instance of `SimpleCache`.

```php
/*
 * This factory creates a SimpleCache instance
 * with MemcachedClient and default configuration
 */
 
$cache = simple_cache_factory('memcached');

/*
 * The configuration directives for the cache client
 * are passed in the second argument as array
 */
 
$cache = simple_cache_factory('redis', [
    'host'       => 'redis',
    'serializer' => 'json',
    'prefix'     => 'test:',
    'ttl'        => 3600 // 1 hour global TTL
]);
```

A bit verbose construction would be

```php
$config = new ConfigFactory(['prefix' => 'test:']);
$client = (new ClientFactory($config))->build('memcached');
$cache  = new SimpleCache($client, 3600);
```

Configuration directives
------------------------

Current available configuration classes

- FileConfiguration
- MemcachedConfiguration
- RedisConfiguration
- PredisConfiguration

### MemcachedConfiguration

| Memcached arguments         | Type     | Required | Description |
|:----------------------------|:---------|----------|:------------|
| id                          | string   | no       | Memcached persistent_id value |
| servers                     | array    | no       | A list of nested array with [server, port] values |
| options                     | array    | no       | A set with Memcached options |

The following options are set **by default** when instance of `MemcachedConfiguration` is created,
except for `OPT_PREFIX_KEY` which is there as a reminder that it may be set

| Memcached Option            | Value                          |
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


License
-------

The code is distributed under the terms of [The 3-Clause BSD license](LICENSE).


[1]: https://redis.io
[2]: https://memcached.org
[3]: https://getcomposer.org
[4]: http://php.net/manual/en/memcached.constants.php
