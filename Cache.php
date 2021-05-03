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

use Psr\SimpleCache\CacheInterface;

interface Cache extends CacheInterface
{
    public const DATE_FAR_FAR_AWAY = 32503593600;

    public const
        E_INVALID_KEY = 1,
        E_UNSUPPORTED_LOGGER = 2,
        E_DIRECTORY_NOT_CREATED = 3,
        E_PHP_EXCEPTION = 4,
        E_CONNECTION_ERROR = 5,
        E_UNSUPPORTED_CLIENT = 6;

    /**
     * Returns the underlying cache client.
     *
     * @return \Memcached | \Redis
     * | \Predis\Client
     * | \Koded\Caching\Client\FileClient
     * | \Koded\Caching\Client\MemoryClient
     * | \Koded\Caching\Client\ShmopClient
     */
    public function client();

    /**
     * Returns the global TTL, if any.
     * Used as default expiration time in cache clients.
     *
     * @return int|null Time in seconds for expiration, or NULL for special cases
     */
    public function getTtl(): ?int;
}
