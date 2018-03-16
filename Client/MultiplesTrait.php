<?php

namespace Koded\Caching\Client;

trait MultiplesTrait
{

    public function getMultiple($keys, $default = null)
    {
        $cached = [];
        foreach ($keys as $key) {
            $cached[$key] = $this->get($key, $default);
        }

        return $cached;
    }

    public function setMultiple($values, $ttl = null)
    {
        if ($ttl < 0 || $ttl === 0) {
            // All items are considered expired and must be deleted
            return $this->deleteMultiple(array_keys($values));
        }

        $cached = 0;
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl) && ++$cached;
        }

        return count($values) === $cached;
    }
}
