<?php
/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 */

namespace Koded\Caching;

use DateInterval;
use DateTimeInterface;
use Koded\Caching\Client\ClientFactory;
use Koded\Caching\Configuration\ConfigFactory;
use Throwable;
use function date_create;
use function is_int;
use function is_iterable;
use function is_string;
use function Koded\Stdlib\now;
use function preg_match;
use function timezone_open;
use function var_export;

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
 * @return Cache
 * @throws CacheException
 */
function simple_cache_factory(string $client = '', array $arguments = []): Cache
{
    try {
        return (new ClientFactory(new ConfigFactory($arguments)))->new($client);
    } catch (Throwable $e) {
        throw CacheException::from($e);
    }
}

/**
 * Guards the cache key value.
 *
 * Differs from PSR-16 by allowing the ":" in the reserved characters list.
 * The colon is a wide accepted delimiter for Redis to "group" the keys.
 *
 * @param string $name The cache key
 *
 * @throws CacheException
 * @see https://github.com/php-cache/integration-tests/issues/92
 */
function verify_key(mixed $name): void
{
    if ('' === $name
        || false === is_string($name)
        || preg_match('/[@\{\}\(\)\/\\\]/', $name)
    ) {
        throw CacheException::forInvalidKey($name);
    }
}

/**
 * Transforms the provided TTL to expiration seconds,
 * or NULL for special cases for cache clients that do
 * not have built-in expiry mechanism.
 *
 * Please use integers as seconds for TTL.
 *
 * @param int|DateInterval|DateTimeInterface|null $value An argument that wants to be a TTL
 *
 * @return int|null Returns the TTL is seconds or NULL
 */
function normalize_ttl(mixed $value): ?int
{
    if (null === $value || is_int($value)) {
        return $value;
    }
    if ($value instanceof DateTimeInterface) {
        return $value->getTimestamp() - now()->getTimestamp();
    }
    if ($value instanceof DateInterval) {
        return date_create('@0', timezone_open('UTC'))
            ->add($value)
            ->getTimestamp();
    }
    throw CacheException::generic('Invalid TTL, given ' . var_export($value, true));
}

/**
 * Filters out the cache item keys and performs a validation on them.
 *
 * @param iterable $iterable    The cache item
 * @param bool     $associative To return an associative array or sequential
 *
 * @return array Valid cache name keys
 */
function filter_keys(mixed $iterable, bool $associative): array
{
    if (/*!is_array($iterable) ||*/ false === is_iterable($iterable)) {
        throw CacheException::forInvalidKey($iterable);
    }
    $keys = [];
    foreach ($iterable as $k => $v) {
        if (false === $associative) {
            verify_key($v);
            $keys[] = $v;
            continue;
        }
        verify_key($k);
        $keys[$k] = $v;
    }
    return $keys;
}
