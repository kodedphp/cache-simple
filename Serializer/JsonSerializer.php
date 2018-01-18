<?php

namespace Koded\Caching\Serializer;

use Koded\Caching\Cache;
use Koded\Caching\CacheException;
use Koded\Caching\CacheSerializer;

class JsonSerializer implements CacheSerializer
{

    public function serialize($value): string
    {
        return json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
    }

    public function unserialize(string $value)
    {
        $json = json_decode($value, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new CacheException(Cache::E_PHP_EXCEPTION, [':message' => json_last_error_msg()]);
        }

        return $json;
    }
}
