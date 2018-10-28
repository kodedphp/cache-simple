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
        $_keys = filter_keys($keys, false);

        return $this->multiGet($_keys, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $_values = filter_keys($values, true);
        $ttl = normalize_ttl($ttl);

        if ($ttl !== null && $ttl < 1) {
            // All items are considered expired and must be deleted
            return $this->deleteMultiple(array_keys($_values));
        }

        return $this->multiSet($_values, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        $_keys = filter_keys($keys, false);

        if (count($_keys)) {
            return $this->multiDelete($_keys);
        }

        return true;
    }

    /*
     *
     * Overridable in specific cache classes.
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
            $this->set($key, $value, $ttl) && $cached++;
        }
        return count($values) === $cached;
    }


    protected function multiDelete(array $keys): bool
    {
        $deleted = 0;
        foreach ($keys as $key) {
            $this->delete($key) && $deleted++;
        }

        return count($keys) === $deleted;
    }
}
