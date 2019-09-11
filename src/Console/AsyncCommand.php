<?php

namespace Barryvdh\Queue\Console;

use Barryvdh\Queue\AsyncQueue;
use Illuminate\Console\Command;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Symfony\Component\Console\Input\InputArgument;

class AsyncCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'queue:async
                            {payload : The payload to construct the Job from}
                            {connection? : The name of connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a queue job from its payload';

    /**
     * The queue worker instance.
     *
     * @var \Illuminate\Queue\Worker
     */
    protected $worker;

    /**
     * Create a new queue listen command.
     *
     * @param  \Illuminate\Queue\Worker  $worker
     */
    public function __construct(Worker $worker)
    {
        parent::__construct();

        $this->worker = $worker;
    }

    /**
     * Execute the console command.
     *
     * @param WorkerOptions $options
     * @return void
     */
    public function handle(WorkerOptions $options)
    {
        $payload = $this->argument('payload');
        $connection = $this->argument('connection');

        $this->processJob(
            $connection, $payload, $options
        );
    }

    /**
     *  Process the job
     *
     * @param string $connectionName
     * @param string $payload
     * @param WorkerOptions $options
     *
     * @throws \Throwable
     */
    protected function processJob($connectionName, $payload, $options)
    {
        $manager = $this->worker->getManager();

        $connection = $manager->connection($connectionName);

        $job = $connection->getJobFromPayload($payload);

        $this->worker->process(
            $manager->getName($connectionName), $job, $options
        );
    }
}
