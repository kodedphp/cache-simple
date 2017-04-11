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

use Koded\Stdlib\Arguments;
use Koded\Stdlib\Interfaces\Configuration;
use Memcached;

/**
 * Class MemcachedConfiguration
 *
 * @see https://github.com/kodedphp/stdlib/blob/master/Interfaces.php#L158
 */
class MemcachedConfiguration extends Arguments implements Configuration
{

    /**
     * MemcachedConfiguration constructor.
     *
     * @param array $options [optional] Memcached options
     *
     * @link http://php.net/manual/en/memcached.constants.php
     */
    public function __construct(array $options = [])
    {
        parent::__construct([
            Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
            Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
            Memcached::OPT_REMOVE_FAILED_SERVERS => true,
            Memcached::OPT_RETRY_TIMEOUT => 1,
            Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
            Memcached::OPT_PREFIX_KEY => null
        ]);

        $this->import($options);
    }

    /**
     * Order of precedence when selecting the servers array
     *
     *  1. "servers" directive that holds an array of memcached servers
     *  2. environment variable "MEMCACHED_POOL" serialized as JSON
     *  3. defaults to one server at localhost:11211
     *
     * @return array Memcached options.
     * The "MEMCACHED_POOL" is ignored if "servers" is provided in the configuration directives.
     *
     * @link http://php.net/manual/en/memcached.addservers.php
     */
    public function getServers(): array
    {
        if ($servers = $this->pull('servers')) {
            return $servers;
        }

        if ($servers = json_decode(getenv('MEMCACHED_POOL'), true)) {
            return $servers;
        }

        return [['127.0.0.1', 11211]];
    }

    /**
     * To add Memcached options
     *
     *  - use the class constructor options argument
     *  - use the Config factory methods (if applicable)
     *  - use the class methods
     *
     * To remove options
     *
     *  - set the option(s) with NULL value
     *  - use the class methods
     *
     * @return array Filtered Memcached options
     */
    public function getOptions(): array
    {
        return array_filter($this->toArray(), function($value) {
            return null !== $value;
        });
    }
}
