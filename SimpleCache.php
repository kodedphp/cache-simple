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

/**
 * Class SimpleCache
 *
 */
class SimpleCache implements Cache
{

    /**
     * @var int The TTL value is seconds, NULL for indefinite, zero or less for expired
     */
    protected $ttl;

    /**
     * @var CacheInterface
     */
    protected $client;

    public function __construct(CacheInterface $client, int $ttl = null)
    {
        $this->client = $client;
        $this->ttl = $ttl;
    }

    public function get(string $key, $default = null)
    {
        return $this->client->get($key, $default);
    }

    public function set(string $key, $value, $ttl = null): bool
    {
        cache_key_guard($key, call_user_func([$this->client, 'keyRegex']));

        return $this->client->set($key, $value, cache_ttl($ttl ?? $this->ttl));
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

    public function setMultiple(iterable $values, $ttl = null): bool
    {
        return $this->client->setMultiple($this->normalizeValues($values), cache_ttl($ttl ?? $this->ttl));
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->client->deleteMultiple($this->normalizeValues($keys));
    }

    public function has(string $key): bool
    {
        return $this->client->has($key);
    }

    public function client()
    {
        return call_user_func([$this->client, 'client']);
    }

    protected function normalizeValues(iterable $values): array
    {
        return ($values instanceof Traversable) ? iterator_to_array($values) : $values;
    }
}
