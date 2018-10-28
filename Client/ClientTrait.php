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

use function Koded\Caching\normalize_ttl;

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

    private function timestampWithGlobalTtl($explicit, int $default = 0): int
    {
        $ttl = normalize_ttl($explicit);

        if (null === $ttl && $this->ttl > 0) {
            return time() + $this->ttl;
        }

        if ($ttl > 0) {
            return time() + $ttl;
        }

        return $ttl ?? $default;
    }

    private function secondsWithGlobalTtl($ttl): int
    {
        $seconds = normalize_ttl($ttl);

        if (null === $seconds && $this->ttl > 0) {
            return $this->ttl;
        }

        return (int)$seconds;
    }
}