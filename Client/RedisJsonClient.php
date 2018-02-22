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

use Koded\Caching\Configuration\RedisConfiguration;
use Koded\Caching\Serializer\{ JsonSerializer, PhpSerializer };
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
class RedisJsonClient extends RedisClient
{

    const SERIALIZED = '__serialized__';

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var PhpSerializer
     */
    protected $phpSerializer;

    public function __construct(Redis $client, RedisConfiguration $config)
    {
        parent::__construct($client, $config);
        $this->jsonSerializer = new JsonSerializer($config->get('options'));
        $this->phpSerializer = new PhpSerializer($config->get('binary', false));
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
