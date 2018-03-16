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
use Koded\Caching\{ CacheException, CacheSerializer };
use Koded\Caching\Configuration\PredisConfiguration;
use Koded\Caching\Serializer\PhpSerializer;
use Predis\Client;
use Predis\Connection\ConnectionException;
use Psr\SimpleCache\CacheInterface;

/**
 * Class PredisClient uses the Predis library.
 *
 * @property Client client
 */
final class PredisClient implements CacheInterface
{

    use ClientTrait, MultiplesTrait;

    /**
     * @var PhpSerializer
     */
    protected $phpSerializer;

    public function __construct(Client $client, PredisConfiguration $config, CacheSerializer $phpSerializer)
    {
        $this->client = $client;
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

    public function delete($key)
    {
        return $this->client->del($key) > 0;
    }

    public function deleteMultiple($keys)
    {
        return $this->client->del($keys) === count($keys);
    }

    public function has($key)
    {
        return (bool)$this->client->exists($key);
    }
}
