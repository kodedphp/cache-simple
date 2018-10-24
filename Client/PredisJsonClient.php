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


final class PredisJsonClient extends RedisJsonClient
{
    public function clear()
    {
        return 'OK' === $this->client->flushAll()->getPayload();
    }

    /*
     *
     * Overrides
     *
     */

    protected function multiDelete(array $keys): bool
    {
        $this->client->del($keys);

        // Is it?
        return true;
    }
}
