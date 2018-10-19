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

namespace Koded\Caching;

use DateInterval;
use DateTime;
use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Psr\SimpleCache\CacheInterface;

/**
 * Factory function for SimpleCache.
 *
 * Example:
 *
 * $cache = simple_cache_factory('redis', ['host' => 'redis']);
 * $cache->get('foo');
 *
 * @param string $client    [optional] The client name (ex. memcached, redis, etc)
 * @param array  $arguments [optional] A configuration parameters for the client
 *
 * @return CacheInterface
 * @throws CacheException
 * @throws \Exception
 */

function simple_cache_factory(string $client = '', array $arguments = []): Cache
{
    return (new ClientFactory(new ConfigFactory($arguments)))->build($client);
}

/**
 * Guards the cache key value.
 *
 * Differs from PSR-16 "reserved" characters by adding the ":" to the list.
 * The colon is a wide accepted convention for Redis to separate the key.
 *
 * @param string $key The cache key
 *
 * @throws CacheException
 */
function cache_key_check(string $key): void
{
    if (1 === preg_match('/[^a-z0-9:_.-]/i', $key)) {
        throw CacheException::forInvalidKey($key);
    }
}

/**
 * Transforms the DateInterval TTL, or return the value as-is.
 *
 * @param null|int|DateInterval $ttl A gypsy argument that wants to be a TTL
 *                                   (apparently a simple number of seconds is not enough and it must be a mess)
 *
 * @return int|null Returns the TTL is seconds, or NULL.
 * Can be a negative number to delete the cached item.
 */
function cache_ttl($ttl): ?int
{
    if (null === $ttl || 0 === $ttl) {
        // because things...
        return $ttl;
    }

    if ($ttl instanceof DateInterval) {
        return (int)((new DateTime)->add($ttl)->format('U')) - time();
    }

    return (int)$ttl;
}
