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

/**
 * Creates once an instance of SimpleCache.
 *
 * If configuration is not provided, defaults to NullClient (dummy) cache client,
 * otherwise it will try to create one defined in the configuration.
 *
 * @param mixed $config [optional] Cache configuration
 *
 * @return SimpleCache
 */
function cache(ConfigurationFactory $config = null): SimpleCache
{
    static $cache;

    if (null === $cache) {
        $config or $config = new ConfigFactory;
        $cache = new SimpleCache((new ClientFactory($config))->build());
    }

    return $cache;
}

/**
 * Guards the cache key value.
 *
 * @param string $key
 * @param string $regex [optional]
 *
 * @return string
 * @throws CacheException
 */
function cache_key_guard(string $key, string $regex = '[^a-z0-9:_\-\/\{\}\[\]\\\.\+\* ]+'): string
{
    if (empty($key) or 1 === preg_match('~' . $regex . '~ui', $key)) {
        throw new CacheException(Cache::E_INVALID_KEY, [':key' => $key]);
    }

    return $key;
}

/**
 * Calculates the TTL according to many things.
 *
 * @param null|int|DateInterval $ttl A gypsy "psr-16" argument that represents a TTL (instead a simple integer)
 *
 * @return int|null Returns a calculated TTL timestamp value, or NULL
 */
function cache_ttl($ttl): ?int
{
    if (null === $ttl) {
        // because things...
        return null;
    }

    if ($ttl instanceof DateInterval) {
        return (new DateTime)->add($ttl)->getTimestamp();
    }

    $ttl = (int)$ttl;

    if ($ttl > 0) {
        return time() + $ttl;
    }

    return $ttl;
}