<?php

namespace Koded\Caching\Client;

use const Koded\Caching\CACHE_DEFAULT_KEY_REGEX;

trait KeyTrait
{

    protected $keyRegex = CACHE_DEFAULT_KEY_REGEX;

    public function keyRegex(): string
    {
        return $this->keyRegex;
    }
}