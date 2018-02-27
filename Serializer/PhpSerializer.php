<?php

namespace Koded\Caching\Serializer;

use Koded\Caching\CacheSerializer;

final class PhpSerializer implements CacheSerializer
{

    private $binary;

    public function __construct(bool $binary)
    {
        $this->binary = $binary && function_exists('igbinary_serialize');
    }

    public function serialize($value): string
    {
        return $this->binary ? igbinary_serialize($value) : serialize($value);
    }

    public function unserialize(string $value)
    {
        return $this->binary ? igbinary_unserialize($value) : unserialize($value);
    }
}
