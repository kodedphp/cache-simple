<?php

namespace Koded\Caching\Client;

use function Koded\Caching\{cache_keys, cache_ttl};

/**
 * Trait MultiplesTrait implements all "multi" operations
 * separated for easy override in the specific cache classes.
 *
 */
trait MultiplesTrait
{

    public function getMultiple($keys, $default = null): iterable
    {
        $keys = cache_keys($keys, false);

        return $this->multiGet($keys, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $values = cache_keys($values, true);
        $ttl = cache_ttl($ttl);

        if ($ttl < 0 || $ttl === 0) {
            // All items are considered expired and must be deleted
            return $this->deleteMultiple(array_keys($values));
        }

        return $this->multiSet($values, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        $keys = cache_keys($keys, false);

        if (empty($keys)) {
            return true;
        }

        return $this->multiDelete($keys);
    }

    /*
     *
     * Overridable in specific cache engines.
     *
     */

    protected function multiGet(array $keys, $default): iterable
    {
        $cached = [];
        foreach ($keys as $key) {
            $cached[$key] = $this->get($key, $default);
        }

        return $cached;
    }


    protected function multiSet(array $values, $ttl): bool
    {
        $cached = 0;
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl) && ++$cached;
        }
        return count($values) === $cached;
    }


    protected function multiDelete(array $keys): bool
    {
        $deleted = 0;
        foreach ($keys as $key) {
            $this->delete($key) && ++$deleted;
        }

        return count($keys) === $deleted;
    }
}
