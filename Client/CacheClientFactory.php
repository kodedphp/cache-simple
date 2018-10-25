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

namespace Koded\Caching\Client;

use Exception;
use Koded\Caching\CacheException;
use Koded\Caching\Configuration\{PredisConfiguration, RedisConfiguration};
use Koded\Stdlib\Interfaces\{ConfigurationFactory, Serializer};
use Koded\Stdlib\Serializer\SerializerFactory;
use Psr\Log\{LoggerInterface, NullLogger};
use Psr\SimpleCache\CacheInterface;


class CacheClientFactory
{

    const CACHE_CLIENT = 'CACHE_CLIENT';

    /**
     * @var ConfigurationFactory
     */
    private $conf;

    public function __construct(ConfigurationFactory $conf)
    {
        $this->conf = $conf;
    }

    /**
     * Create an instance of specific cache client.
     *
     * @param string $client The required cache client
     *
     * @return CacheInterface An instance of the cache client
     * @throws CacheException
     * @throws Exception
     */
    public function build(string $client = ''): CacheInterface
    {
        $client = strtolower($client ?: getenv(self::CACHE_CLIENT) ?: '');
        $config = $this->conf->build($client);

        switch ($client) {
            case 'redis':
                /** @var \Koded\Caching\Configuration\RedisConfiguration $config */
                return $this->getRedisClient($config);

            case 'memcached':
                /** @var \Koded\Caching\Configuration\MemcachedConfiguration $config */
                return new MemcachedClient(new \Memcached($config->get('id')), $config);

            case 'predis':
                /** @var \Koded\Caching\Configuration\PredisConfiguration $config */
                return $this->getPredisClient($config);

            case 'file':
                /** @var \Koded\Caching\Configuration\FileConfiguration $config */
                return new FileClient($this->getLogger($config), (string)$config->get('dir'), $config->get('ttl'));

            default:
                return new MemoryClient($config->get('ttl'));
        }
    }


    private function getRedisClient(RedisConfiguration $conf): CacheInterface
    {
        $serializer = $conf->get('serializer');

        if (Serializer::JSON === $serializer && $conf->get('binary')) {
            return new RedisJsonClient(
                $this->createRedisClient($conf),
                SerializerFactory::new((string)$conf->get('binary'), $conf->get('options')),
                (int)$conf->get('options'),
                $conf->get('ttl')
            );
        }

        return new RedisClient(
            $this->createRedisClient($conf),
            SerializerFactory::new($serializer, $conf->get('options')),
            $conf->get('ttl')
        );
    }


    private function getPredisClient(PredisConfiguration $conf): CacheInterface
    {
        $serializer = $conf->get('serializer');

        if (Serializer::JSON === $serializer && $conf->get('binary')) {
            return new PredisJsonClient(
                $this->createPredisClient($conf),
                SerializerFactory::new((string)$conf->get('binary'), $conf->get('options')),
                (int)$conf->get('options'),
                $conf->get('ttl')
            );
        }

        return new PredisClient(
            $this->createPredisClient($conf),
            SerializerFactory::new($conf->get('serializer'), $conf->get('options')),
            $conf->get('ttl')
        );
    }

    /**
     * Initialize a Redis client with configuration.
     *
     * @param RedisConfiguration $conf
     *
     * @return \Redis
     * @throws CacheException
     */
    private function createRedisClient(RedisConfiguration $conf): \Redis
    {
        try {
            $client = new \Redis;

            // Because connect() does not throw exception, but E_WARNING
            if (false === @$client->connect(...$conf->getConnectionParams())) {
                // @codeCoverageIgnoreStart
                throw CacheException::withConnectionErrorFor('Redis');
                // @codeCoverageIgnoreEnd
            }

            $client->setOption(\Redis::OPT_SERIALIZER, $conf->get('type'));
            $client->setOption(\Redis::OPT_PREFIX, $conf->get('prefix'));
            $client->select((int)$conf->get('db', 0));

            if ($auth = $conf->get('auth')) {
                $client->auth($auth);
            }

            return $client;

        } catch (\RedisException $e) {
            error_log($e->getMessage());
            throw CacheException::withConnectionErrorFor('Redis');
        } catch (CacheException $e) {
            throw $e;
        } catch (Exception $e) {
            throw CacheException::generic($e->getMessage(), $e);
        }
    }

    /**
     * Creates a Predis\Client and connects it to Redis server.
     *
     * @param PredisConfiguration $conf
     *
     * @return \Predis\Client
     * @throws CacheException
     */
    private function createPredisClient(PredisConfiguration $conf): \Predis\Client
    {
        try {
            $client = new \Predis\Client($conf->getConnectionParams(), $conf->getOptions());
            $client->connect();
            $client->select((int)$conf->get('db', 0));

            if ($auth = $conf->get('auth')) {
                $client->auth($auth);
            }

            return $client;

        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (\Predis\Connection\ConnectionException $e) {
            throw CacheException::withConnectionErrorFor('Predis', $e);
        } catch (Exception $e) {
            throw CacheException::generic($e->getMessage(), $e);
        }
    }


    private function getLogger($conf): LoggerInterface
    {
        $logger = $conf->logger ?? new NullLogger;

        if (!$logger instanceof LoggerInterface) {
            throw CacheException::forUnsupportedLogger(LoggerInterface::class, get_class($logger));
        }

        return $logger;
    }
}
