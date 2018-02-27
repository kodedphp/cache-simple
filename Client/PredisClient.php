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
use Koded\Caching\Configuration\PredisConfiguration;
use Koded\Caching\Serializer\PhpSerializer;
use Predis\Client;
use Predis\Connection\ConnectionException;
use Psr\SimpleCache\CacheInterface;

/**
 * Class PredisClient uses the Predis library.
 *
 */
final class PredisClient implements CacheInterface
{

    use ClientTrait, RedisTrait;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var PhpSerializer
     */
    protected $phpSerializer;

    public function __construct(Client $client, PredisConfiguration $config, CacheSerializer $phpSerializer)
    {
        $this->client = $client;
        $this->keyRegex = $config->get('keyRegex', $this->keyRegex);
        $this->phpSerializer = $phpSerializer;

        try {
            $this->client->connect();
            $this->client->select((int)$this->get('db'));

            if ($auth = $config->get('auth')) {
                $this->client->auth($auth);
            }
        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (ConnectionException $e) {
            throw CacheException::withConnectionErrorFor('Predis', $e);
        } catch (Exception $e) {
            throw CacheException::generic($e->getMessage(), $e);
        }
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->phpSerializer->unserialize($this->client->get($key));
        }

        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        if (null === $ttl) {
            return 'OK' === $this->client->set($key, $this->phpSerializer->serialize($value))->getPayload();
        }

        if ($ttl > 0) {
            return 'OK' === $this->client->setex($key, $ttl, $this->phpSerializer->serialize($value))->getPayload();
        }

        // The item is considered expired and must be deleted
        $this->client->del($key);

        return !$this->has($key);
    }

    public function clear()
    {
        return 'OK' === $this->client->flushall()->getPayload();
    }
}
