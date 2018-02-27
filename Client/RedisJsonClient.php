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
use Koded\Caching\CacheSerializer;
use Koded\Caching\Configuration\RedisConfiguration;
use Koded\Caching\Serializer\{ JsonSerializer, PhpSerializer };
use Psr\SimpleCache\CacheInterface;
use Redis;

/**
 * Class RedisJsonClient uses the Redis PHP extension to save the cache item as JSON.
 *
 * It will create 2 entries in Redis, one as JSON cache item and other as serialized PHP value.
 * The first is useful for other programming languages to use it,
 * and the PHP serialized variant is useful only for PHP applications
 * where the cached item is handled with serialization.
 *
 */
final class RedisJsonClient implements CacheInterface
{

    use RedisTrait, ClientTrait;

    const SERIALIZED = '__serialized__';

    /**
     * @var Redis
     */
    private $client;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var PhpSerializer
     */
    private $phpSerializer;

    public function __construct(
        Redis $client,
        RedisConfiguration $config,
        CacheSerializer $jsonSerializer,
        CacheSerializer $phpSerializer
    ) {
        $this->client = $client;
        $this->keyRegex = $config->get('keyRegex', $this->keyRegex);
        $this->jsonSerializer = $jsonSerializer;
        $this->phpSerializer = $phpSerializer;

        try {
            // Because connect() does not throw exception, but E_WARNING
            if (false === @$this->client->connect(...$config->getConnectionParams())) {
                throw CacheException::withConnectionErrorFor('Redis');
            }

            $this->client->setOption(Redis::OPT_SERIALIZER, $config->getSerializerType());
            $this->client->setOption(Redis::OPT_PREFIX, $config->get('prefix'));
            $this->client->select((int)$this->get('db'));

            if ($auth = $config->get('auth')) {
                $this->client->auth($auth);
            }
        } catch (CacheException $e) {
            throw $e;
        } catch (Exception $e) {
            throw CacheException::generic($e->getMessage(), $e);
        }
    }

    public function get($key, $default = null)
    {
        if ($this->client->exists($key . self::SERIALIZED)) {
            return $this->phpSerializer->unserialize($this->client->get($key . self::SERIALIZED));
        }

        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        if (null === $ttl) {
            return $this->client->set($key, $this->jsonSerializer->serialize($value))
                && $this->client->set($key . self::SERIALIZED, $this->phpSerializer->serialize($value));
        }

        if ($ttl > 0) {
            return $this->client->setex($key, $ttl, $this->jsonSerializer->serialize($value))
                && $this->client->setex($key . self::SERIALIZED, $ttl, $this->phpSerializer->serialize($value));
        }

        // The item is considered expired and must be deleted
        $this->client->del($key, $key . self::SERIALIZED);

        return !$this->has($key);
    }

    public function has($key)
    {
        return (bool)$this->client->exists($key)
            && (bool)$this->client->exists($key . self::SERIALIZED);
    }

    public function delete($key)
    {
        return $this->client->del($key, $key . self::SERIALIZED) > 0;
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $keys[] = $key . self::SERIALIZED;
        }

        return $this->client->del($keys) > 0;
    }
}
