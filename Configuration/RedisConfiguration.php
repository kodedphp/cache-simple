<?php declare(strict_types=1);

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

use Koded\Caching\{ Cache, CacheException };
use Koded\Stdlib\Immutable;
use Koded\Stdlib\Interfaces\Configuration;

class RedisConfiguration extends Immutable implements Configuration
{

    /**
     *
     * @return array [
     * string $host can be a host, or the path to a unix domain socket
     * int   $port           optional
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

    /**
     * @return array [
     * int $serializer The serializer type
     * callable $setter The setter
     * callable $getter The getter
     * ]
     */
    public function getSerializerParams(): array
    {
        $serializer = $this->get('serializer', Cache::SERIALIZER_PHP);

        if (Cache::SERIALIZER_PHP === $serializer) {
            return [
                \Redis::SERIALIZER_PHP,
                function($value) {
                    return serialize($value);
                    return $value;
                },
                function(string $value) {
                    return unserialize($value);
                    return $value;
                }
            ];
        }

        if (Cache::SERIALIZER_BINARY === $serializer) {
            return [
                \Redis::SERIALIZER_IGBINARY,
                function($value) {
                    return igbinary_serialize($value);
                },
                function(string $value) {
                    return igbinary_unserialize($value);
                }
            ];
        }

        if (Cache::SERIALIZER_JSON === $serializer) {
            return [
                \Redis::SERIALIZER_NONE,
                function($value) {
                    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                },
                function(string $value) {
                    return json_decode($value, true);
                }
            ];
        }

        throw new CacheException(Cache::E_INVALID_SERIALIZER, [':type' => $serializer]);
    }
}
