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

    /**
     * Returns the underlying cache client.
     *
     * @return \Memcached | \Redis | \Koded\Caching\Client\FileClient | \Predis\Client
     */
    public function client();
}

interface CacheSerializer extends Serializer
{
}
