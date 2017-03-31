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

use Koded\Exceptions\KodedException;
use Psr\SimpleCache\InvalidArgumentException;

class CacheException extends KodedException implements InvalidArgumentException
{
    protected $messages = [
        Cache::E_INVALID_KEY => 'The cache key is invalid, given ":key"',
        Cache::E_UNSUPPORTED_LOGGER => 'The cache logger should be NULL or an instance of :supported, given :given',
        Cache::E_UNSUPPORTED_NORMALIZER => 'The data normalizer is not supported: ":name"',
        Cache::E_EXTENSION_NOT_ENABLED => 'The :name extension is not enabled',
        Cache::E_DIRECTORY_NOT_CREATED => 'Failed to create a cache directory ":dir".',
    ];
}
