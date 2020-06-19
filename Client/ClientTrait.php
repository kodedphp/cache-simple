<?php
/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 */

namespace Koded\Caching\Client;

use function Koded\Caching\normalize_ttl;
use function Koded\Stdlib\now;

trait ClientTrait
{
    /** @var int|null Global TTL for caching, used as default expiration time in cache clients */
    private $ttl;

    /** @var \Memcached | \Redis | \Predis\Client | \Koded\Caching\Client\FileClient | \Koded\Caching\Client\MemoryClient | \Koded\Caching\Client\ShmopClient */
    private $client;

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function client()
    {
        return $this->client ?? $this;
    }

    private function timestampWithGlobalTtl($ttl, int $default = 0): int
    {
        $explicit = normalize_ttl($ttl);
        $now = now()->getTimestamp();

        if (null === $explicit && $this->ttl > 0) {
            return $now + $this->ttl;
        }

        if ($explicit > 0) {
            return $now + $explicit;
        }

        return $explicit ?? $default;
    }

    private function secondsWithGlobalTtl($ttl): int
    {
        $seconds = normalize_ttl($ttl);

        if (null === $seconds && $this->ttl > 0) {
            return (int)$this->ttl;
        }

        return (int)$seconds;
    }
}