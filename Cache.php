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

use Koded\Stdlib\Interfaces\Serializer;
use Psr\SimpleCache\CacheInterface;


interface Cache extends CacheInterface
{

    const E_INVALID_KEY = 1;
    const E_UNSUPPORTED_LOGGER = 2;
    const E_DIRECTORY_NOT_CREATED = 3;
    const E_PHP_EXCEPTION = 4;
    const E_CONNECTION_ERROR = 5;

    const A_DATE_FAR_FAR_AWAY = 32503593600;

    /**
     * Returns the underlying cache client.
     *
     * @return \Memcached | \Redis | \Koded\Caching\Client\FileClient | \Predis\Client
     */
    public function client();

    /**
     * Returns the global TTL, if any.
     * Used as default expiration time in cache clients.
     *
     * @return int|null Time in seconds for expiration, or NULL for special cases
     */
    public function getTtl(): ?int;

    /**
     * Sets the global TTL for caching.
     * Used as default expiration time in cache clients.
     *
     * @param int|null|\DateInterval|\DateTimeInterface $ttl A mess of possible values for the TTL
     *
     * @return Cache
     */
    public function setTtl($ttl): Cache;
}
