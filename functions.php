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

use Koded\Exceptions\CacheException;

function cache_key(string $key): string
{
    if (empty($key)) {
        throw new CacheException(Cache::E_INVALID_KEY, [':key' => $key]);
    }

    return $key;
}

function cache($config = null): SimpleCache
{
    static $cache;

    if (null === $cache) {
        $cache = new SimpleCache(CacheClientFactory::build($config), $config);
    }

    return $cache;
}
