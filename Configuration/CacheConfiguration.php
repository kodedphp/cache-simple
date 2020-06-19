<?php
/*
 * This file is part of the Koded package.
 *
 * (c) Mihail Binev <mihail@kodeart.com>
 *
 * Please view the LICENSE distributed with this source code
 * for the full copyright and license information.
 */

namespace Koded\Caching\Configuration;

use Koded\Stdlib\Config;

abstract class CacheConfiguration extends Config
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(array $parameters = [])
    {
        $parameters and $this->import($parameters);
    }
}
