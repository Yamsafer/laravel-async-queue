<?php

namespace Barryvdh\Queue\Connectors;

use Barryvdh\Queue\AsyncQueue;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Support\Arr;

class AsyncConnector implements ConnectorInterface
{

    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config = [])
    {
        return new AsyncQueue(
            Arr::get($config, 'binary', 'php'),
            Arr::get($config, 'binary_args', ''),
            Arr::get($config, 'connection_name', '')
        );
    }
}
