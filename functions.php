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
use Koded\Caching\Configuration\ConfigFactory;
use Koded\Stdlib\Interfaces\ConfigurationFactory;

const CACHE_DEFAULT_KEY_REGEX = '[^a-z0-9:_\-\.]+';

/**
 * Creates once an instance of SimpleCache.
 *
 * If configuration is not provided, defaults to NullClient (dummy) cache client,
 * otherwise it will try to create one defined in the configuration.
 *
 * @param mixed $config [optional] Cache configuration
 *
 * @return SimpleCache
 * @throws CacheException
 * @throws \Exception
 */
function cache(ConfigurationFactory $config = null): SimpleCache
{
    static $cache;

    if (null === $cache) {
        $config || $config = new ConfigFactory;
        $cache = new SimpleCache((new ClientFactory($config))->build(), $config->ttl);
    }

    return $cache;
}

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
 * @return SimpleCache
 * @throws CacheException
 * @throws \Exception
 */

function simple_cache_factory(string $client = '', array $arguments = []): SimpleCache
{
    $config = new ConfigFactory($arguments);
    return new SimpleCache((new ClientFactory($config))->build($client), $config->ttl);
}

/**
 * Guards the cache key value.
 *
 * @param string $key   The cache key
 * @param string $regex [optional] Allowed characters for the cache key
 *
 * @return string
 * @throws CacheException
 */
function cache_key_guard(string $key, string $regex = CACHE_DEFAULT_KEY_REGEX): string
{
    if (empty($key) || 1 === preg_match('~' . $regex . '~i', $key)) {
        throw new CacheException(Cache::E_INVALID_KEY, [':key' => $key]);
    }

    return $key;
}

/**
 * Transforms the DateInterval TTL, or return the value as-is.
 *
 * @param null|int|DateInterval $ttl A gypsy argument that wants to be a TTL
 *                                   (apparently a simple integer is not enough)
 *
 * @return int|null Returns the TTL is seconds, or NULL. Can be a negative number to delete cache items
 */
function cache_ttl($ttl): ?int
{
    if (null === $ttl || 0 === $ttl) {
        // because things...
        return $ttl;
    }

    if ($ttl instanceof DateInterval) {
        return (new DateTime)->add($ttl)->getTimestamp() - time();
    }

    $ttl = (int)$ttl;

    if ($ttl > 0) {
        return $ttl;
    }

    return $ttl;
}
