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

use Koded\Stdlib\{Arguments, Config};
use Koded\Stdlib\Interfaces\Configuration;
use Throwable;

/**
 * Class ConfigFactory
 *
 * @property int|null $ttl Default Time-To-Live seconds for the cached items
 *
 */
class ConfigFactory extends Config
{

    public function __construct(array $parameters = [])
    {
        parent::__construct();
        $this->import($parameters);
    }

    public function build(string $context): Configuration
    {
        try {
            $class = join('\\', [__NAMESPACE__, ucfirst($context) . 'Configuration']);
            return new $class($this->toArray());
        } catch (Throwable $e) {
            return new class($this->toArray()) extends Arguments implements Configuration {};
        }
    }
}
