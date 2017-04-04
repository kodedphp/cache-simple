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
use Koded\Stdlib\Interfaces\Configuration;
use Memcached;

/**
 * Class MemcachedConfiguration
 *
 * @todo Implement this class properly
 */
class MemcachedConfiguration extends Immutable implements Configuration
{

    public function getServers(): array
    {
        if (!$servers = json_decode(getenv('MEMCACHED_POOL'), true)) {
            $servers = [['127.0.0.1', 11211]];
        }

        return $servers;
    }

    public function getOptions(): array
    {
        return [
            Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
            Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
            Memcached::OPT_REMOVE_FAILED_SERVERS => true,
            Memcached::OPT_RETRY_TIMEOUT => 1,
            Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
            Memcached::OPT_PREFIX_KEY => ''
        ];
    }
}