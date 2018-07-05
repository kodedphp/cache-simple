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

use Koded\Caching\Client\{ FileClient, MemcachedClient, MemoryClient, NullClient, PredisClient, RedisClient, RedisJsonClient };
use Koded\Caching\Serializer\{ JsonSerializer, PhpSerializer };
use Koded\Stdlib\Interfaces\ConfigurationFactory;
use Psr\Log\{ LoggerInterface, NullLogger };
use Psr\SimpleCache\CacheInterface;

class ClientFactory
{

    const CACHE_CLIENT = 'CACHE_CLIENT';

    /**
     * @var ConfigurationFactory
     */
    private $config;

    public function __construct(ConfigurationFactory $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $client The required cache client
     *
     * @return CacheInterface An instance of the cache client
     * @throws CacheException
     * @throws \Exception
     */
    public function build(string $client = ''): CacheInterface
    {
        $client = strtolower($client ?: getenv(self::CACHE_CLIENT) ?: '');
        $config = $this->config->build($client);

        if ('redis' === $client) {
            /** @var \Koded\Caching\Configuration\RedisConfiguration $config */
            if (Cache::SERIALIZER_JSON === $config->get('serializer')) {
                return new RedisJsonClient(
                    new \Redis,
                    $config,
                    new JsonSerializer,
                    new PhpSerializer($config->get('binary', false))
                );
            }

            return new RedisClient(new \Redis, $config);
        }

        if ('memcached' === $client) {
            /** @var \Koded\Caching\Configuration\MemcachedConfiguration $config */
            return new MemcachedClient(new \Memcached($config->get('id')), $config);
        }

        if ('predis' === $client) {
            /** @var \Koded\Caching\Configuration\PredisConfiguration $config */
            return new PredisClient(
                new \Predis\Client($config->getConnectionParams(), $config->getOptions()),
                $config,
                new PhpSerializer($config->get('binary', false))
            );
        }

        if ('file' === $client) {
            /** @var \Koded\Caching\Configuration\FileConfiguration $config */
            return new FileClient($config, $this->getLogger($config));
        }

        if ('memory' === $client) {
            return new MemoryClient;
        }

        return new NullClient;
    }

    private function getLogger($config): LoggerInterface
    {
        $logger = $config->logger ?? new NullLogger;

        if (!$logger instanceof LoggerInterface) {
            throw CacheException::forUnsupportedLogger(LoggerInterface::class, get_class($logger));
        }

        return $logger;
    }
}
