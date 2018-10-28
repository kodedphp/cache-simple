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
use DateTimeInterface;
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
 */

function simple_cache_factory(string $client = '', array $arguments = []): Cache
{
    try {
        return (new CacheClientFactory(new ConfigFactory($arguments)))->build($client);
    } catch (Exception $ex) {
        throw CacheException::from($ex);
    }
}

/**
 * Guards the cache key value.
 *
 * Differs from PSR-16 by allowing the ":" in the reserved characters list.
 * The colon is a wide accepted convention for Redis to "group" the key.
 *
 * @param string $name The cache key
 *
 * @throws CacheException
 * @see https://github.com/php-cache/integration-tests/issues/92
 */
function verify_key($name): void
{
    /*
     * This hack exists because the bug in the integration tests.
     * $this->cache->setMultiple(['0' => 'value0'])
     * assumes "0" is a valid key for a key name
     */
    if (0 === $name) {
        return;
    }

    if ('' === $name || false === is_string($name)) {
        throw CacheException::forInvalidKey($name);
    }

    try {
        if (preg_match('/[@\{\}\(\)\/\\\]+/', $name)) {
            throw CacheException::forInvalidKey($name);
        }
    } catch (Throwable $e) {
        throw CacheException::forInvalidKey($name);
    }
}

/**
 * Filters out the cache items keys and performs a validation on them.
 *
 * @param iterable $iterable    The cache item
 * @param bool     $associative To return an associative array or sequential
 *
 * @return array Valid cache name keys
 */
function filter_keys($iterable, bool $associative): array
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
        array_walk($keys, '\Koded\Caching\verify_key');
    } else {
        array_walk($_ = array_keys($keys), '\Koded\Caching\verify_key');
    }

    return $keys;
}

/**
 * Transforms the provided TTL to integer (seconds) or NULL.
 * Please use integers as seconds for expiration.
 *
 * @param int|DateInterval|DateTimeInterface|null $value A gypsy argument that wants to be a TTL.
 *                                                       Can be a negative number to delete the cached item
 *
 * @return int|null Returns the TTL is seconds, or NULL
 */
function normalize_ttl($value): ?int
{
    if (null === $value || is_int($value)) {
        return $value;
    }

    if ($value instanceof DateTimeInterface) {
        return $value->getTimestamp();
    }

    if ($value instanceof DateInterval) {
        return date_create('@0')->add($value)->getTimestamp();
    }

    throw CacheException::generic('Invalid TTL, given ' . var_export($value, true));
}
