<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2022/10/16 16:06:57
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace library;

class Process
{
    protected mixed $process;

    /**
     * Process constructor.
     * @param string $startCmd
     * @param int $signalNo
     */
    public function __construct(protected string $startCmd, protected int $signalNo)
    {
    }

    /**
     * Create process
     * @access public
     * @return $this
     */
    public function create(): static
    {
        $this->process = proc_open($this->startCmd, [], $pipes);
        return $this;
    }

    /**
     * Start process
     * @access public
     * @return $this
     */
    public function start(): static
    {
        $this->create();
        return $this;
    }

    /**
     * Stop process
     * @access public
     * @return $this
     */
    public function stop(): static
    {
        if (is_resource($this->process)) {
            $this->kill();
            proc_close($this->process);
        }
        unset($this->process);
        return $this;
    }

    /**
     * Restart process
     * @access public
     * @return $this
     */
    public function restart(): static
    {
        $this->stop();
        $this->start();
        return $this;
    }

    /**
     * Detect the running status of the child process
     * Exit the master process if the child process exits
     * @access public
     * @return void
     */
    public function isRunning(): void
    {
        if (!is_resource($this->process) || !proc_get_status($this->process)['running']) {
            echo 'The child process has ended, and the main process is ready to exit...' . PHP_EOL;
            proc_close($this->process);
            die();
        }
    }

    /**
     * Kill process
     * @access public
     * @return $this
     */
    public function kill(): static
    {
        // Get process status
        $info = proc_get_status($this->process);

        if (!$info['running']) {
            return $this;
        }

        // Run on Windows platform
        if (PHP_OS === 'WINNT') {
            // use taskkill to kill process
            shell_exec('taskkill /F /T /PID ' . $info['pid']);
            return $this;
        }

        if (function_exists('posix_kill')) {
            // Use posix_kill to kill process
            // posix_kill is only supported on Linux platforms
            posix_kill($info['pid'], $this->signalNo);
        } else {
            // Use shell_exec to kill process
            proc_terminate($this->process, $this->signalNo);
        }

        return $this;
    }
}
