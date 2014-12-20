<?php

namespace Barryvdh\Queue\Connectors;

use Barryvdh\Queue\AsyncQueue;
use Illuminate\Queue\Connectors\DatabaseConnector;

class AsyncConnector implements DatabaseConnector
{

    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new AsyncQueue(
			$this->connections->connection(array_get($config, 'connection')),
			$config['table'],
			$config['queue'],
			array_get($config, 'expire', 60),
            array_get($config, 'binary', 'php'),
            array_get($config, 'binary_args', '')
		);
    }
}
