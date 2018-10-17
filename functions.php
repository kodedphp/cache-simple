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
use Koded\Stdlib\Interfaces\ConfigurationFactory;
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

function simple_cache_factory(string $client = '', array $arguments = []): CacheInterface
{
    $config = new ConfigFactory($arguments);
    return (new ClientFactory($config))->build($client);
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
function guard_cache_key(string $key): void
{
    if (1 === preg_match('/[^a-z0-9:_.-]/i', $key)) {
        throw CacheException::forInvalidKey($key);
    }
}

/**
 * Transforms the DateInterval TTL, or return the value as-is.
 *
 * @param null|int|DateInterval $ttl A gypsy argument that wants to be a TTL
 *                                   (apparently a simple timestamp is not enough)
 *
 * @return int|null Returns the TTL is seconds, or NULL. Can be a negative number to delete the cached items
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

/**
 * Creates once an instance of SimpleCache.
 *
 * If configuration is not provided, defaults to NullClient (dummy) cache client,
 * otherwise it will try to create one defined in the configuration.
 *
 * @param ConfigurationFactory|null $config [optional] Cache configuration
 *
 * @return CacheInterface
 * @throws CacheException | \Exception
 */
function cache(ConfigurationFactory $config = null): CacheInterface
{
    static $cache;

    if (null === $cache) {
        $config || $config = new ConfigFactory;
        $cache = (new ClientFactory($config))->build();
    }

    return $cache;
}
