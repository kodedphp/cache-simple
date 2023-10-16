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

use function array_keys;
use function Koded\Caching\filter_keys;
use function Koded\Caching\normalize_ttl;

/**
 * Trait MultiplesTrait implements all "multi" operations
 * separated for easy override in the specific cache classes.
 *
 */
trait MultiplesTrait
{
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $filtered = filter_keys($keys, false);
        return $this->internalMultiGet($filtered, $default);
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $filtered = filter_keys($values, true);
        $ttl = normalize_ttl($ttl ?? $this->ttl);
        if ($ttl !== null && $ttl < 1) {
            // All items are considered expired and must be deleted
            return $this->deleteMultiple(array_keys($filtered));
        }
        return $this->internalMultiSet($filtered, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $filtered = filter_keys($keys, false);
        if (empty($filtered)) {
            return true;
        }
        return $this->internalMultiDelete($filtered);
    }

    /*
     *
     * Override in specific cache classes.
     *
     */

    private function internalMultiGet(array $keys, mixed $default): iterable
    {
        $cached = [];
        foreach ($keys as $key) {
            $cached[$key] = $this->get($key, $default);
        }
        return $cached;
    }

    private function internalMultiSet(array $values, ?int $ttl): bool
    {
        $cached = $count = 0;
        foreach ($values as $key => $value) {
            $count++;
            $this->set($key, $value, $ttl) && ++$cached;
        }
        return $count === $cached;
    }

    private function internalMultiDelete(array $keys): bool
    {
        $deleted = $count = 0;
        foreach ($keys as $key) {
            $count++;
            $this->delete($key) && ++$deleted;
        }
        return $count === $deleted;
    }
}
