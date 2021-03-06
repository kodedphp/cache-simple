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

use Koded\Stdlib\Serializer;

final class PredisConfiguration extends CacheConfiguration
{
    public function __construct(array $values)
    {
        $values += [
            'serializer' => Serializer::PHP,
            'binary' => Serializer::PHP,
        ];
        parent::__construct($values);
    }

    public function getConnectionParams(): array
    {
        return [
            'scheme' => $this->get('scheme', 'tcp'),
            'host' => $this->get('host', '127.0.0.1'),
            'port' => $this->get('port', 6379)
        ];
    }

    public function getOptions(): ?array
    {
        return $this->get('options');
    }
}
