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

use Koded\Caching\Client\{ FileClient, MemcachedClient, PredisClient, RedisClient, NullClient };
use Koded\Stdlib\Interfaces\ConfigurationFactory;
use Psr\Log\{ LoggerInterface, NullLogger };
use Psr\SimpleCache\CacheInterface;

class ClientFactory
{

    const CACHE_CLIENT = 'CACHE_CLIENT';

    /** @var ConfigurationFactory */
    private $config;

    /**
     * ClientFactory constructor.
     *
     * @param ConfigurationFactory $config
     */
    public function __construct(ConfigurationFactory $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $client
     *
     * @return CacheInterface
     * @throws CacheException
     * @throws \Exception
     */
    public function build(string $client = ''): CacheInterface
    {
        $client = strtolower($client ?: getenv(self::CACHE_CLIENT) ?: null);
        $config = $this->config->build($client);

        if ('redis' === $client) {
            /** @var \Koded\Caching\Configuration\RedisConfiguration $config */
            return new RedisClient($config);
        }

        if ('memcached' === $client) {
            /** @var \Koded\Caching\Configuration\MemcachedConfiguration $config */
            return new MemcachedClient($config);
        }

        if ('predis' === $client) {
            /** @var \Koded\Caching\Configuration\PredisConfiguration $config */
            return new PredisClient($config);
        }

        if ('file' === $client) {
            /** @var \Koded\Caching\Configuration\FileConfiguration $config */
            return new FileClient($config, $this->getLogger($config));
        }

        return new NullClient;
    }

    private function getLogger($config): LoggerInterface
    {
        $logger = $config->logger ?? new NullLogger;

        if (!$logger instanceof LoggerInterface) {
            throw new CacheException(Cache::E_UNSUPPORTED_LOGGER, [
                ':supported' => LoggerInterface::class,
                ':given' => get_class($logger)
            ]);
        }

        return $logger;
    }
}
