<?php

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
     * Overridable in specific cache classes.
     *
     */

    protected function internalMultiGet(array $keys, $default): iterable
    {
        $cached = [];
        foreach ($keys as $key) {
            $cached[$key] = $this->get($key, $default);
        }

        return $cached;
    }


    protected function internalMultiSet(array $values, $ttl): bool
    {
        $cached = 0;
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl) && $cached++;
        }
        return count($values) === $cached;
    }


    protected function internalMultiDelete(array $keys): bool
    {
        $deleted = 0;
        foreach ($keys as $key) {
            $this->delete($key) && $deleted++;
        }

        return count($keys) === $deleted;
    }
}
