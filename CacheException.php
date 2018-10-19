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

use Exception;
use Koded\Exceptions\KodedException;
use Psr\SimpleCache\InvalidArgumentException;

class CacheException extends KodedException implements InvalidArgumentException
{
    protected $messages = [
        Cache::E_INVALID_KEY => 'The cache key is invalid, ":key" given',
        Cache::E_UNSUPPORTED_LOGGER => 'The cache logger should be NULL or an instance of :supported, :given given',
        Cache::E_DIRECTORY_NOT_CREATED => 'Failed to create a cache directory ":dir"',
        Cache::E_PHP_EXCEPTION => '[Cache Exception] :message',
        Cache::E_CONNECTION_ERROR => '[Cache Exception] Failed to connect the :client client',
    ];

    public static function forInvalidKey(string $key)
    {
        return new self(Cache::E_INVALID_KEY, [':key' => $key]);
    }

    public static function forUnsupportedLogger(string $supported, string $given)
    {
        return new self(Cache::E_UNSUPPORTED_LOGGER, [':supported' => $supported, ':given' => $given]);
    }

    public static function forCreatingDirectory(string $directory)
    {
        return new static(Cache::E_DIRECTORY_NOT_CREATED, [':dir' => $directory]);
    }

    public static function generic(string $message, Exception $previous = null)
    {
        return new self(Cache::E_PHP_EXCEPTION, [':message' => $message], $previous);
    }

    public static function withConnectionErrorFor(string $clientName, Exception $previous = null)
    {
        return new self(Cache::E_CONNECTION_ERROR, [':client' => $clientName], $previous);
    }
}
