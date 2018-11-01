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

namespace Koded\Caching\Configuration;

use Koded\Stdlib\Immutable;
use Koded\Stdlib\Interfaces\{Configuration, Serializer};

final class RedisConfiguration extends Immutable implements Configuration
{

    private $type;

    public function __construct(array $values)
    {
        parent::__construct($values + [
                'serializer' => $serializer = $values['serializer'] ?? Serializer::PHP,
                'binary' => $values['binary'] ?? Serializer::PHP
            ]);

        switch ($serializer) {
            case Serializer::PHP:
                $this->type = \Redis::SERIALIZER_PHP;
                break;

            case Serializer::IGBINARY:
                $this->type = \Redis::SERIALIZER_IGBINARY;
                break;

            default:
                $this->type = \Redis::SERIALIZER_NONE;
        }
    }

    /**
     *
     * @return array [
     * string $host can be a host, or the path to a unix domain socket
     * int   $port           optional, default 6379
     * float $timeout        value in seconds (optional, default is 0.0 meaning unlimited)
     * null  $reserved       should be null if $retry_interval is specified
     * int   $retry_interval retry interval in milliseconds.
     * ]
     *
     */
    public function getConnectionParams(): array
    {
        return [
            $this->get('host', '127.0.0.1'),
            $this->get('port', 6379),
            $this->get('timeout', 0.0),
            $this->get('reserved', null),
            $this->get('retry', 0)
        ];
    }
}
