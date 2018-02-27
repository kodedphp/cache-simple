<?php

namespace Koded\Caching\Serializer;

use Koded\Caching\{ CacheException, CacheSerializer };

final class JsonSerializer implements CacheSerializer
{

    public function serialize($value): string
    {
        return json_encode($value,
            JSON_PRESERVE_ZERO_FRACTION
            | JSON_NUMERIC_CHECK
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );
    }

    public function unserialize(string $value)
    {
        $json = json_decode($value, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw CacheException::generic(json_last_error_msg());
        }

        return $json;
    }
}
