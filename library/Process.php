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

    public function __construct(protected string $startCmd, protected int $signalNo)
    {
    }

    public function create(): static
    {
        $this->process = proc_open($this->startCmd, [], $pipes);
        return $this;
    }

    public function start(): static
    {
        $this->create();
        return $this;
    }

    public function stop(): static
    {
        if (is_resource($this->process)) {
            $this->kill();
            proc_close($this->process);
        }
        unset($this->process);
        return $this;
    }

    public function restart(): static
    {
        $this->stop();
        $this->start();
        return $this;
    }

    public function isRunning(): void
    {
        if (!is_resource($this->process) || !proc_get_status($this->process)['running']) {
            echo 'The child process has ended, and the main process is ready to exit...' . PHP_EOL;
            proc_close($this->process);
            die();
        }
    }

    public function kill(): static
    {
        $info = proc_get_status($this->process);

        if (!$info['running']) {
            return $this;
        }

        if (PHP_OS === 'WINNT') {
            shell_exec('taskkill /F /T /PID ' . $info['pid']);
            return $this;
        }

        if (function_exists('posix_kill')) {
            posix_kill($info['pid'], $this->signalNo);
        } else {
            proc_terminate($this->process, $this->signalNo);
        }

        return $this;
    }
}
