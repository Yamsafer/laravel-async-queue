<?php
namespace Barryvdh\Queue;

use Illuminate\Database\Connection;
use Illuminate\Queue\SyncQueue;
use Illuminate\Queue\Jobs\SyncJob;
use Symfony\Component\Process\Process;

class AsyncQueue extends SyncQueue
{
    /** @var string */
    protected $binary;

    /** @var string */
    protected $binaryArgs;

    /** @var string */
    protected $connectionName;

    /**
     * @param  string  $binary
     * @param  string|array  $binaryArgs
     * @param  string  $connectionName
     */
    public function __construct($binary = 'php', $binaryArgs = '', $connectionName = '')
    {
        $this->binary = $binary;
        $this->binaryArgs = $binaryArgs;
        $this->connectionName = $connectionName;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string      $job
     * @param mixed       $data
     * @param string|null $queue
     *
     * @return int
     */
    public function push($job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $queue, $data);

        $this->startProcess($payload);

        return 0;
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     *
     * @return int
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->startProcess($payload);

        return 0;
    }

    /**
     * Get the next available job for the queue.
     *
     * @param  int $id
     *
     * @return SyncJob
     */
    public function getJobFromPayload($payload)
    {
        return new SyncJob(
            $this->container, $payload, $this->connectionName, 'async'
        );
    }

    /**
     * Make a Process for the Artisan command for the job id.
     *
     * @param string $payload
     *
     * @return void
     */
    public function startProcess($payload)
    {
        $command = $this->getCommand($payload);
        $cwd = base_path();

        $process = new Process($command, $cwd);
        $process->run();
    }

    /**
     * Get the Artisan command as a string for the job id.
     *
     * @param string $payload
     *
     * @return string
     */
    protected function getCommand($payload)
    {
        if ( ! defined('PHP_WINDOWS_VERSION_BUILD')) {
            $payload = escapeshellarg($payload);
        }

        $connection = $this->connectionName;
        $cmd = '%s artisan queue:async %s %s';
        $cmd = $this->getBackgroundCommand($cmd);

        $binary = $this->getPhpBinary();

        return sprintf($cmd, $binary, $payload, $connection);
    }

    /**
     * Get the escaped PHP Binary from the configuration
     *
     * @return string
     */
    protected function getPhpBinary()
    {
        $path = $this->binary;
        if ( ! defined('PHP_WINDOWS_VERSION_BUILD')) {
            $path = escapeshellarg($path);
        }

        $args = $this->binaryArgs;
        if (is_array($args)) {
            $args = implode(' ', $args);
        }

        return trim($path . ' ' . $args);
    }

    /**
     * Get input command but modified to run in the background
     *
     * @param string $cmd
     *
     * @return string
     */
    protected function getBackgroundCommand($cmd)
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            return 'start /B ' . $cmd . ' > NUL';
        }

        return $cmd . ' > /dev/null 2>&1 &';
    }
}
