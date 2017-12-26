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
        Cache::E_INVALID_KEY => 'The cache key is invalid, ":key" given',
        Cache::E_INVALID_TTL => 'Invalid cache TTL value. Cannot set an expiration time in the past',
        Cache::E_UNSUPPORTED_LOGGER => 'The cache logger should be NULL or an instance of :supported, :given given',
        Cache::E_EXTENSION_NOT_ENABLED => 'The :name extension is not enabled',
        Cache::E_DIRECTORY_NOT_CREATED => 'Failed to create a cache directory ":dir"',
        Cache::E_INVALID_SERIALIZER => 'Invalid cache serializer ":type"',
        Cache::E_PHP_EXCEPTION => '[Cache Exception] :message :stacktrace',
    ];
}
