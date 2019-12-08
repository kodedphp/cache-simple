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
        Cache::E_INVALID_KEY => 'The cache key is invalid, given (:type) :key',
        Cache::E_UNSUPPORTED_LOGGER => 'The cache logger should be NULL or an instance of :supported, given :given',
        Cache::E_DIRECTORY_NOT_CREATED => 'Failed to create a cache directory ":dir"',
        Cache::E_PHP_EXCEPTION => '[Cache Exception] :message',
        Cache::E_CONNECTION_ERROR => '[Cache Exception] Failed to connect the :client client',
        Cache::E_UNSUPPORTED_CLIENT => '[Cache Exception] Unsupported cache client :name',
    ];

    public static function forInvalidKey($key)
    {
        return new self(Cache::E_INVALID_KEY, [':key' => var_export($key, true), ':type' => gettype($key)]);
    }


    public static function forUnsupportedLogger(string $supported, string $given)
    {
        return new self(Cache::E_UNSUPPORTED_LOGGER, [':supported' => $supported, ':given' => $given]);
    }


    public static function forCreatingDirectory(string $directory)
    {
        return new self(Cache::E_DIRECTORY_NOT_CREATED, [':dir' => $directory]);
    }


    public static function generic(string $message, Exception $previous = null)
    {
        return new self(Cache::E_PHP_EXCEPTION, [':message' => $message], $previous);
    }


    public static function withConnectionErrorFor(string $clientName)
    {
        return new self(Cache::E_CONNECTION_ERROR, [':client' => $clientName]);
    }

    public static function forUnsupportedClient(string $client)
    {
        return new self(Cache::E_UNSUPPORTED_CLIENT, [':name' => $client]);
    }
}
