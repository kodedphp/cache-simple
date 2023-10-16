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
use Memcached;
use function array_filter;
use function array_replace;
use function class_exists;
use function getenv;
use function json_decode;

/**
 * Class MemcachedConfiguration
 *
 * @see https://github.com/kodedphp/stdlib/blob/master/Interfaces.php#L158
 */
final class MemcachedConfiguration extends CacheConfiguration
{
    /**
     * MemcachedConfiguration constructor.
     *
     * @param array $options [optional] Memcached options. Used here:
     *
     * OPT_DISTRIBUTION          - consistent, if one node goes down it's keys are distributed to other nodes
     * OPT_CONNECT_TIMEOUT       - milliseconds after server is considered dead
     * OPT_SERVER_FAILURE_LIMIT  - number of connection failures before server is marked as dead and removed
     * OPT_REMOVE_FAILED_SERVERS - (bool) to remove dead server or not
     * OPT_RETRY_TIMEOUT         - try a dead server after this seconds (tweak for long running processes)
     *
     * @link http://php.net/manual/en/memcached.constants.php
     */
    public function __construct(array $options = [])
    {
        // @codeCoverageIgnoreStart
        if (false === class_exists('\Memcached', false)) {
            throw CacheException::generic('Memcached extension is not loaded on this machine.');
        }
        // @codeCoverageIgnoreEnd

        parent::__construct([
            'id' => $options['id'] ?? null,
            'servers' => $options['servers'] ?? [],
            'options' => array_replace([
                Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
                Memcached::OPT_CONNECT_TIMEOUT => 10,
                Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
                Memcached::OPT_REMOVE_FAILED_SERVERS => true,
                Memcached::OPT_RETRY_TIMEOUT => 1,
                Memcached::OPT_PREFIX_KEY => null
            ], $options['options'] ?? []),
            'ttl' => $options['ttl'] ?? null
        ]);
    }

    /**
     * Order of precedence when selecting the servers array
     *
     *  1. "servers" directive that holds an array of memcached servers
     *  2. environment variable "MEMCACHED_POOL" serialized as JSON [['ip', port],...]
     *  3. defaults to one server at localhost:11211
     *
     * @return array Memcached options.
     * The "MEMCACHED_POOL" is ignored if "servers" is provided in the configuration directives.
     *
     * @link http://php.net/manual/en/memcached.addservers.php
     */
    public function getServers(): array
    {
        if ($servers = $this->get('servers')) {
            return $servers;
        }
        if ($servers = json_decode(getenv('MEMCACHED_POOL'), true)) {
            return $servers;
        }
        return [
            ['127.0.0.1', 11211]
        ];
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
        return array_filter($this->toArray()['options'], fn($value) => null !== $value);
    }

    /**
     * Returns the global TTL in seconds, or NULL for never-expire value.
     *
     * @return int|null
     */
    public function getTtl(): ?int
    {
        if (null === $ttl = $this->get('ttl')) {
            return null;
        }
        return (int)$ttl;
    }
}
