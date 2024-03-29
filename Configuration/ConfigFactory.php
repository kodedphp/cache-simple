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

use Koded\Stdlib\{Config, Configuration};
use Koded\Caching\CacheException;
use Throwable;
use function join;
use function ucfirst;

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
        $parameters && $this->import($parameters);
    }

    public function build(string $context): Configuration
    {
        try {
            $class = join('\\', [__NAMESPACE__, ucfirst($context) . 'Configuration']);
            return new $class($this->toArray());
        // @codeCoverageIgnoreStart
        } catch (CacheException $e) {
            throw $e;
        // @codeCoverageIgnoreEnd
        } catch (Throwable) {
            return new class($this->toArray()) extends CacheConfiguration {};
        }
    }
}
