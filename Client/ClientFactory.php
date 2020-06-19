<?php
/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 */

namespace Koded\Caching\Client;

use Error;
use Exception;
use Koded\Caching\{Cache, CacheException};
use Koded\Caching\Configuration\{MemcachedConfiguration, PredisConfiguration, RedisConfiguration};
use Koded\Stdlib\{Configuration, Serializer};
use Koded\Stdlib\Serializer\SerializerFactory;
use Psr\Log\{LoggerInterface, NullLogger};


final class ClientFactory
{
    public const CACHE_CLIENT = 'CACHE_CLIENT';

    private $factory;

    public function __construct(Configuration $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Create an instance of specific cache client.
     *
     * @param string $client The required cache client
     *                       (memcached, redis, predis, shmop, file, memory)
     *
     * @return Cache An instance of the cache client
     * @throws CacheException
     * @throws Exception
     */
    public function new(string $client = ''): Cache
    {
        $client = strtolower($client ?: getenv(self::CACHE_CLIENT) ?: 'memory');
        $config = $this->factory->build($client);

        switch ($client) {
            case 'memcached':
                /** @var MemcachedConfiguration $config */
                return $this->createMemcachedClient($config);
            case 'redis':
                /** @var RedisConfiguration $config */
                return $this->createRedisClient($config);
            case 'predis':
                /** @var PredisConfiguration $config */
                return $this->createPredisClient($config);
            case 'shmop':
                return new ShmopClient(
                    (string)$config->get('dir'),
                    $config->get('ttl')
                );
            case 'file':
                return new FileClient(
                    $this->getLogger($config),
                    (string)$config->get('dir'),
                    $config->get('ttl')
                );
            case 'memory':
                return new MemoryClient($config->get('ttl'));
        }

        throw CacheException::forUnsupportedClient($client);
    }


    private function createMemcachedClient(MemcachedConfiguration $conf): Cache
    {
        $client = new \Memcached($conf->get('id'));
        $client->setOptions($conf->getOptions());

        if (empty($client->getServerList())) {
            $client->addServers($conf->getServers());
        }

        return new MemcachedClient($client, $conf->getTtl());
    }


    private function createRedisClient(RedisConfiguration $conf): Cache
    {
        $serializer = $conf->get('serializer');
        $binary = $conf->get('binary');

        if (Serializer::JSON === $serializer && $binary) {
            return new RedisJsonClient(
                $this->newRedisClient($conf),
                SerializerFactory::new((string)$binary, ...$conf->get('options', [0])),
                (int)$conf->get('options'),
                $conf->get('ttl')
            );
        }

        return new RedisClient(
            $this->newRedisClient($conf),
            SerializerFactory::new($serializer, ...$conf->get('options', [0])),
            $conf->get('ttl')
        );
    }


    private function createPredisClient(PredisConfiguration $conf): Cache
    {
        $serializer = $conf->get('serializer');
        $binary = $conf->get('binary');

        if (Serializer::JSON === $serializer && $binary) {
            return new PredisJsonClient(
                $this->newPredisClient($conf),
                SerializerFactory::new((string)$binary, ...$conf->get('options', [0])),
                (int)$conf->get('options'),
                $conf->get('ttl')
            );
        }

        return new PredisClient(
            $this->newPredisClient($conf),
            SerializerFactory::new($conf->get('serializer'), ...$conf->get('options', [0])),
            $conf->get('ttl')
        );
    }


    private function newRedisClient(RedisConfiguration $conf): \Redis
    {
        try {
            $client = new \Redis;
            @$client->connect(...$conf->getConnectionParams());

            $client->setOption(\Redis::OPT_SERIALIZER, $conf->get('type'));
            $client->setOption(\Redis::OPT_PREFIX, $conf->get('prefix'));
            $client->select((int)$conf->get('db'));

            if ($auth = $conf->get('auth')) {
                $client->auth($auth);
            }

            return $client;

        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (\RedisException $e) {
            error_log('[Redis] ' . $e->getMessage());
            throw CacheException::withConnectionErrorFor('Redis');
        } catch (Exception | Error $e) {
            throw CacheException::from($e);
        }
    }


    private function newPredisClient(PredisConfiguration $conf): \Predis\Client
    {
        try {
            $client = new \Predis\Client($conf->getConnectionParams(), $conf->getOptions());
            $client->connect();

            $client->select((int)$conf->get('db'));

            if ($auth = $conf->get('auth')) {
                $client->auth($auth);
            }

            return $client;

        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (\Predis\Connection\ConnectionException $e) {
            error_log('[Predis] ' . $e->getMessage());
            throw CacheException::withConnectionErrorFor('Predis');
        } catch (Exception $e) {
            throw CacheException::from($e);
        }
    }

    /**
     * @param Configuration $conf
     *
     * @return \Psr\Log\LoggerInterface
     */
    private function getLogger($conf): LoggerInterface
    {
        $logger = $conf->logger ?? new NullLogger;

        if ($logger instanceof LoggerInterface) {
            return $logger;
        }

        throw CacheException::forUnsupportedLogger(LoggerInterface::class, get_class($logger));
    }
}