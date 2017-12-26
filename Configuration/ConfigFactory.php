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

use const Koded\Caching\CACHE_DEFAULT_KEY_REGEX;
use Koded\Stdlib\{ Arguments, Config, Interfaces\Configuration };

/**
 * Class ConfigFactory
 *
 * @property int $ttl Default Time-To-Live seconds for the cached items
 * @property string $keyRegex Regex for validating the cache item key
 */
class ConfigFactory extends Config
{

    public function __construct(array $parameters = [])
    {
        parent::__construct();

        $this->set('keyRegex', CACHE_DEFAULT_KEY_REGEX);
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