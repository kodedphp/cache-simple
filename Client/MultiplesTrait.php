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

use function Koded\Caching\{filter_keys, normalize_ttl};

/**
 * Trait MultiplesTrait implements all "multi" operations
 * separated for easy override in the specific cache classes.
 *
 */
trait MultiplesTrait
{
    public function getMultiple($keys, $default = null): iterable
    {
        $filtered = filter_keys($keys, false);

        return $this->internalMultiGet($filtered, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $filtered = filter_keys($values, true);
        $ttl = normalize_ttl($ttl ?? $this->ttl);

        if ($ttl !== null && $ttl < 1) {
            // All items are considered expired and must be deleted
            return $this->deleteMultiple(array_keys($filtered));
        }

        return $this->internalMultiSet($filtered, $ttl);
    }

    public function deleteMultiple($keys): bool
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

    /**
     * @param array $keys
     * @param mixed $default
     *
     * @return iterable
     */
    private function internalMultiGet(array $keys, $default): iterable
    {
        $cached = [];
        foreach ($keys as $key) {
            $cached[$key] = $this->get($key, $default);
        }

        return $cached;
    }

    /**
     * @param array    $values
     * @param int|null $ttl
     *
     * @return bool
     */
    private function internalMultiSet(array $values, $ttl): bool
    {
        $cached = 0;
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl) && ++$cached;
        }
        return count($values) === $cached;
    }

    /**
     * @param array $keys
     *
     * @return bool
     */
    private function internalMultiDelete(array $keys): bool
    {
        $deleted = 0;
        foreach ($keys as $key) {
            $this->delete($key) && ++$deleted;
        }

        return count($keys) === $deleted;
    }
}
