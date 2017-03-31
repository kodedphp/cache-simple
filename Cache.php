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

use Psr\SimpleCache\InvalidArgumentException;

interface Cache
{

    const E_INVALID_KEY = 1;
    const E_UNSUPPORTED_LOGGER = 2;
    const E_UNSUPPORTED_NORMALIZER = 3;
    const E_EXTENSION_NOT_ENABLED = 4;
    const E_DIRECTORY_NOT_CREATED = 5;

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default [optional] Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function get(string $key, $default = null);

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string       $key            The key of the item to store.
     * @param mixed        $value          The value of the item to store, must be serializable.
     * @param int $ttl            [optional] The TTL value of this item. If no value is sent and
     *                                     the driver supports TTL then the library may set a default value
     *                                     for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function set(string $key, $value, int $ttl = 0): bool;

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function delete(string $key): bool;

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool;

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default [optional] Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs.
     *                  Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable, or if any of the
     *                                  $keys are not a legal value.
     */
    public function getMultiple(iterable $keys, $default = null): iterable;

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable     $values          A list of key => value pairs for a multiple-set operation.
     * @param int $ttl             [optional] The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException MUST be thrown if $values is neither an array nor a Traversable, or if any of
     *                                  the $values are not a legal value.
     */
    public function setMultiple(iterable $values, int $ttl = 0): bool;

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable, or if any of the
     *                                  $keys are not a legal value.
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function has(string $key): bool;
}