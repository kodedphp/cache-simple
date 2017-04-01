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

use Psr\SimpleCache\CacheInterface;
use Traversable;

class SimpleCache implements Cache
{

    /** @var int */
    protected $ttl = 0;

    /** @var CacheInterface */
    protected $client;

    public function __construct(CacheInterface $client, $config = null)
    {
        $this->client = $client;
        $this->ttl = $config['ttl'] ?? 0;
    }

    public function get(string $key, $default = null)
    {
        return $this->client->get($key, $default);
    }

    public function set(string $key, $value, int $ttl = 0): bool
    {
        if ($ttl < 0) {
            throw new CacheException(Cache::E_INVALID_TTL, []);
        }
        return $this->client->set($key, $value, $ttl ?: $this->ttl);
    }

    public function delete(string $key): bool
    {
        return $this->client->delete($key);
    }

    public function clear(): bool
    {
        return $this->client->clear();
    }

    public function getMultiple(iterable $keys, $default = null): iterable
    {
        return $this->client->getMultiple($this->normalizeValues($keys), $default);
    }

    public function setMultiple(iterable $values, int $ttl = 0): bool
    {
        return $this->client->setMultiple($this->normalizeValues($values), $ttl ?: $this->ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->client->deleteMultiple($this->normalizeValues($keys));
    }

    public function has(string $key): bool
    {
        return $this->client->has($key);
    }

    protected function normalizeValues(iterable $values): array
    {
        return ($values instanceof Traversable) ? iterator_to_array($values) : $values;
    }
}