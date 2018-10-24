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
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Koded\Caching\Client\CacheClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Psr\SimpleCache\CacheInterface;
use Throwable;

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
 * @throws Exception
 */

function simple_cache_factory(string $client = '', array $arguments = []): Cache
{
    return (new CacheClientFactory(new ConfigFactory($arguments)))->build($client);
}

/**
 * Guards the cache key value.
 *
 * Differs from PSR-16 by allowing the ":" in the reserved characters list.
 * The colon is a wide accepted convention for Redis to "group" the key.
 *
 * @param string $key The cache key
 *
 * @throws CacheException
 * @see https://github.com/php-cache/integration-tests/issues/92
 */
function cache_key_check($key): void
{
    /*
     * This hack exists because the bug in the integration tests.
     * $this->cache->setMultiple(['0' => 'value0'])
     * assumes "0" is a valid key for the key name, which is not
     */
    if (0 === $key) {
        return;
    }

    if ('' === $key || false === is_string($key)) {
        throw CacheException::forInvalidKey($key);
    }

    try {
        if (preg_match('/[@\{\}\(\)\/\\\]/', $key)) {
            throw CacheException::forInvalidKey($key);
        }
    } catch (Throwable $e) {
        throw CacheException::forInvalidKey($key);
    }
}

/**
 * Filters out the cache item keys and performs a validation on them.
 *
 * @param iterable $iterable    The cache item
 * @param bool     $associative To return an associative array or sequential
 *
 * @return array Valid cache name keys
 */
function cache_keys($iterable, bool $associative): array
{
    if (false === is_iterable($iterable)) {
        throw CacheException::forInvalidKey($iterable);
    }

    $keys = [];
    foreach ($iterable as $key => $value) {
        if (false === $associative) {
            $keys[] = $value;
            continue;
        }

        if (false === is_string($key) && false === is_int($key) && false === is_float($key)) {
            throw CacheException::forInvalidKey($key);
        }
        $keys[$key] = $value;
    }

    // Validate the keys

    if (false === $associative) {
        array_walk($keys, '\Koded\Caching\cache_key_check');
    } else {
        $_keys = array_keys($keys);
        array_walk($_keys, '\Koded\Caching\cache_key_check');
    }

    return $keys;
}

/**
 * Transforms the DateInterval TTL, or return the value as-is.
 * Please use integers as seconds for expiration.
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

    if (is_int($ttl)) {
        return $ttl;
    }

    if ($ttl instanceof DateTimeInterface) {
        return (int)$ttl->format('U');
    }

    if ($ttl instanceof DateInterval) {
        return (int)((new DateTime('now', new DateTimeZone('UTC')))->add($ttl)->format('U')) - time();
    }

    throw CacheException::generic('Invalid TTL, given ' . var_export($ttl, true));
}
