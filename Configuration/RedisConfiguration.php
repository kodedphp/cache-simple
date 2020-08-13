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

use Koded\Stdlib\Serializer;

final class RedisConfiguration extends CacheConfiguration
{
    private $type;

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

        switch ($values['serializer']) {
            case Serializer::PHP:
                $this->type = \Redis::SERIALIZER_PHP;
                break;
            // @codeCoverageIgnoreStart
            case Serializer::IGBINARY:
                $this->type = \Redis::SERIALIZER_IGBINARY;
                break;
            // @codeCoverageIgnoreEnd

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
