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

    /** @var \Memcached | \Redis | \Koded\Caching\Client\FileClient | \Predis\Client */
    protected $client;

    /** @var int|null */
    protected $ttl;

    public function client()
    {
        return $this->client ?? $this;
    }

    public function setTtl($ttl): Cache
    {
        $this->ttl = cache_ttl($ttl);

        return $this;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }
}