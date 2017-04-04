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

namespace Koded\Caching\Configuration;

use Koded\Stdlib\Arguments;
use Koded\Stdlib\Config;
use Koded\Stdlib\Interfaces\Configuration;

class ConfigFactory extends Config
{

    public function __construct(array $parameters = [])
    {
        parent::__construct(getcwd());
        $this->import($parameters);
    }

    public function build(string $context): Configuration
    {
        if (empty($context)) {
            return new class([]) extends Arguments implements Configuration {};
        }

        $class = join('\\', [__NAMESPACE__, ucfirst($context) . 'Configuration']);
        return new $class($this->toArray());
    }
}