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

    private Configuration $factory;

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
        $client = \strtolower($client ?: \getenv(self::CACHE_CLIENT) ?: 'memory');
        $config = $this->factory->build($client);

        return match ($client) {
            'memory' => new MemoryClient($config->get('ttl')),
            'memcached' => $this->createMemcachedClient($config),
            'redis' => $this->createRedisClient($config),
            'predis' => $this->createPredisClient($config),
            'shmop' => new ShmopClient((string)$config->get('dir'), $config->get('ttl')),
            'file' => new FileClient($this->getLogger($config), (string)$config->get('dir'), $config->get('ttl')),
            default => throw CacheException::forUnsupportedClient($client)
        };
    }

    private function createMemcachedClient(MemcachedConfiguration|Configuration $conf): Cache
    {
        $client = new \Memcached($conf->get('id'));
        $client->setOptions($conf->getOptions());
        if (empty($client->getServerList())) {
            $client->addServers($conf->getServers());
        }
        return new MemcachedClient($client, $conf->getTtl());
    }

    private function createRedisClient(RedisConfiguration|Configuration $conf): Cache
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

    private function createPredisClient(PredisConfiguration|Configuration $conf): Cache
    {
        $binary = $conf->get('binary');
        if (Serializer::JSON === $conf->get('serializer') && $binary) {
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
        $client = new \Redis;
        try {
            @$client->connect(...$conf->getConnectionParams());
            $client->setOption(\Redis::OPT_SERIALIZER, $conf->get('type'));
            $client->setOption(\Redis::OPT_PREFIX, $conf->get('prefix'));
            $client->select((int)$conf->get('db'));
            if ($auth = $conf->get('auth')) {
                $client->auth($auth);
            }

        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (\RedisException $e) {
            if (!\strpos($e->getMessage(), ' AUTH ')) {
                \error_log(\sprintf(PHP_EOL . '[Redis] %s: %s', \get_class($e), $e->getMessage()));
                \error_log('[Redis] with conf: ' . $conf->toJSON());
                throw CacheException::withConnectionErrorFor('Redis');
            }
        } catch (Exception | Error $e) {
            throw CacheException::from($e);
        }
        return $client;
    }

    private function newPredisClient(PredisConfiguration $conf): \Predis\Client
    {
        $client = new \Predis\Client($conf->getConnectionParams(), $conf->getOptions());

        try {
            $client->connect();
            $client->select((int)$conf->get('db'));
            if ($auth = $conf->get('auth')) {
                $client->auth($auth);
            }

        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (\Predis\Connection\ConnectionException $e) {
            if (!\strpos($e->getMessage(), ' AUTH ')) {
                \error_log(\sprintf(PHP_EOL . '[Predis] %s: %s', \get_class($e), $e->getMessage()));
                \error_log('[Predis] with conf: ' . $conf->toJSON());
                throw CacheException::withConnectionErrorFor('Predis');
            }
        } catch (Exception $e) {
            throw CacheException::from($e);
        }
        return $client;
    }

    private function getLogger(Configuration $conf): LoggerInterface
    {
        $logger = $conf->logger ?? new NullLogger;
        if ($logger instanceof LoggerInterface) {
            return $logger;
        }
        throw CacheException::forUnsupportedLogger(LoggerInterface::class, \get_class($logger));
    }
}
