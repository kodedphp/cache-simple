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

trait ClientTrait
{

    /** @var int|null Global TTL for caching, used as default expiration time in cache clients */
    private $ttl;

    /** @var \Memcached | \Redis | \Predis\Client | \Koded\Caching\Client\FileClient | \Koded\Caching\Client\MemoryClient */
    private $client;

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function client()
    {
        return $this->client ?? $this;
    }
}