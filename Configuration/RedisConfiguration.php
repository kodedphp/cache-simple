<?php
/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 */

namespace Koded\Caching\Configuration;

use Koded\Caching\CacheException;
use Koded\Stdlib\Serializer;
use Redis;
use function class_exists;

final class RedisConfiguration extends CacheConfiguration
{
    private int $type;

    public function __construct(array $values)
    {
        // @codeCoverageIgnoreStart
        if (false === class_exists('\Redis', false)) {
            throw CacheException::generic('Redis extension is not loaded on this machine.');
        }
        // @codeCoverageIgnoreEnd

        $values += [
            'serializer' => Serializer::PHP,
            'binary' => Serializer::PHP,
        ];

        parent::__construct($values);

        $this->type = match ($values['serializer']) {
            Serializer::PHP => Redis::SERIALIZER_PHP,
            Serializer::IGBINARY => Redis::SERIALIZER_IGBINARY,
            default => Redis::SERIALIZER_NONE,
        };
    }

    /**
     * Returns connection parameters for Redis client.
     *
     * string $host           can be a host, or the path to a unix domain socket
     * int    $port           optional, default 6379
     * float  $timeout        value in seconds (optional, default is 0.0 meaning unlimited)
     * null   $reserved       should be null if $retry_interval is specified
     * int    $retry          retry interval in milliseconds.
     * float  $readTimeout    value in seconds (optional, default is 0 meaning unlimited)
     *
     * @return array{host:string, port:int, timeout:float, reserved:null, retry:int, readTimeout:float}
     */
    public function getConnectionParams(): array
    {
        return [
            $this->get('host', '127.0.0.1'),
            $this->get('port', 6379),
            $this->get('timeout', 0.0),
            $this->get('reserved', null),
            $this->get('retry', 0),
            $this->get('readTimeout', 0.0),
        ];
    }
}
