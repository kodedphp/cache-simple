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

use Koded\Caching\Configuration\PredisConfiguration;
use Predis\Client;
use Psr\SimpleCache\CacheInterface;

/**
 * Class PredisClient uses the Predis library.
 *
 */
class PredisClient extends RedisClient implements CacheInterface
{

    /**
     * @var Client
     */
    protected $client;

    /** @noinspection PhpMissingParentConstructorInspection
     *
     * PredisClient constructor.
     *
     * @param PredisConfiguration $config
     */
    public function __construct(PredisConfiguration $config)
    {
        $this->keyRegex = $config->get('keyRegex', $this->keyRegex);
        $this->setNormalizers($config->get('normalizer', ''));
        $this->client = new Client($config->getParameters(), $config->getOptions());
    }

    public function clear()
    {
        return $this->client->flushall()->getPayload() === 'OK';
    }

    protected function setNormalizers(string $normalizer)
    {
        if ('json' === $normalizer) {
            $this->serialize = function(string $key, $value, $ttl = null): bool {
                $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

                if ($ttl < 0 or $ttl === 0) {
                    // The item is considered expired and must be deleted
                    $this->delete($key);

                    return true;
                }

                if (null === $ttl) {
                    return $this->client->set($key, json_encode($value, $options))->getPayload() === 'OK';
                }

                return $this->client->setex($key, $ttl, json_encode($value))->getPayload() === 'OK';
            };

            $this->unserialize = function(string $key) {
                return json_decode($this->client->get($key), true);
            };

        } else {
            $this->serialize = function(string $key, $value, $ttl = null): bool {
                if ($ttl < 0 or $ttl === 0) {
                    // The item is considered expired and must be deleted
                    $this->delete($key);

                    return true;
                }

                if (null === $ttl) {
                    return $this->client->set($key, serialize($value))->getPayload() === 'OK';
                }

                return $this->client->setex($key, $ttl, unserialize($value))->getPayload() === 'OK';
            };

            $this->unserialize = function(string $key) {
                return unserialize($this->client->get($key));
            };
        }
    }
}