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

use Koded\Caching\Cache;
use function Koded\Caching\cache_ttl;

trait ClientTrait
{

    /** @var int|null */
    protected $ttl;

    /** @var \Memcached | \Redis | \Predis\Client | \Koded\Caching\Client\FileClient | \Koded\Caching\Client\MemoryClient */
    protected $client;

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTtl($ttl): Cache
    {
        $this->ttl = cache_ttl($ttl);

        return $this;
    }

    public function client()
    {
        return $this->client ?? $this;
    }
}