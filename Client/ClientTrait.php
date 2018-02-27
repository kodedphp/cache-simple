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

use const Koded\Caching\CACHE_DEFAULT_KEY_REGEX;

trait ClientTrait
{

    private $keyRegex = CACHE_DEFAULT_KEY_REGEX;

    public function client()
    {
        return $this->client ?? $this;
    }

    public function keyRegex(): string
    {
        return $this->keyRegex;
    }
}