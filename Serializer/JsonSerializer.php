<?php

namespace Koded\Caching\Serializer;

use Koded\Caching\{
    Cache, CacheException, CacheSerializer
};

class JsonSerializer implements CacheSerializer
{

    /**
     * @var int JSON encode options. Defaults to (1376):
     *          - JSON_PRESERVE_ZERO_FRACTION
     *          - JSON_NUMERIC_CHECK
     *          - JSON_UNESCAPED_SLASHES
     *          - JSON_UNESCAPED_UNICODE
     */
    private $options;

    public function __construct(int $options = null)
    {
        $this->options = $options ??
            JSON_PRESERVE_ZERO_FRACTION
            | JSON_NUMERIC_CHECK
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        ;
    }

    public function serialize($value): string
    {
        return json_encode($value, $this->options);
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
