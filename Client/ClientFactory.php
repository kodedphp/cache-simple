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

use Exception;
use Koded\Caching\{Cache, CacheException};
use Memcached;
use Koded\Caching\Configuration\{MemcachedConfiguration, PredisConfiguration, RedisConfiguration};
use Koded\Stdlib\{Configuration, Serializer};
use Koded\Stdlib\Serializer\SerializerFactory;
use Psr\Log\{LoggerInterface, NullLogger};
use Redis;
use Throwable;
use function error_log;
use function getenv;
use function sprintf;
use function strtolower;

final class ClientFactory
{
    public const CACHE_CLIENT = 'CACHE_CLIENT';

    public function __construct(private Configuration $factory) {}

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
        $client = new Memcached($conf->get('id'));
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

    private function newRedisClient(RedisConfiguration $conf): Redis
    {
        $client = new Redis;
        try {
            @$client->connect(...$conf->getConnectionParams());
            $client->setOption(Redis::OPT_SERIALIZER, $conf->get('type'));
            $client->setOption(Redis::OPT_PREFIX, $conf->get('prefix'));
            $client->select((int)$conf->get('db'));
            if ($auth = $conf->get('auth')) {
                $client->auth($auth);
            }
            if ($message = $client->getLastError()) {
                // [NOTE] Redis module complains if auth is set,
                // but <Redis v5 or less> does not have auth
                throw new Exception($message);
            }
            return $client;

        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (Throwable $e) {
            error_log(sprintf(PHP_EOL . '[Redis] %s: %s', $e::class, $e->getMessage()));
            error_log('[Redis] with conf: ' . $conf->delete('auth')->toJSON());
            throw CacheException::withConnectionErrorFor('Redis');
        }
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
            return $client;

        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (Throwable $e) {
            error_log(sprintf(PHP_EOL . '[Predis] %s: %s', $e::class, $e->getMessage()));
            error_log('[Predis] with conf: ' . $conf->delete('auth')->toJSON());
            throw CacheException::withConnectionErrorFor('Predis');
        }
    }

    private function getLogger(Configuration $conf): LoggerInterface
    {
        $logger = $conf->logger ?? new NullLogger;
        if ($logger instanceof LoggerInterface) {
            return $logger;
        }
        throw CacheException::forUnsupportedLogger(LoggerInterface::class, $logger::class);
    }
}
