<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2022/10/21 21:59:51
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

use library\File;
use library\Helper;
use library\Process;

require __DIR__ . '/vendor/autoload.php';

Mix\Cli\Cli::setName('fly-watch')->setVersion('1.0.0-alpha');

$cmd = new Mix\Cli\Command([
    'name' => 'run',
    'short' => 'Run file hot update',
    'singleton' => true,
    'run' => function () {
        $include = Mix\Cli\Flag::match('i', 'include')->string();
        $exclude = Mix\Cli\Flag::match('e', 'exclude')->string();
        $interval = Mix\Cli\Flag::match('t', 'interval')->float(2);
        $startCmd = Mix\Cli\Flag::match('c', 'cmd')->string();
        $signal = Mix\Cli\Flag::match('s', 'signal')->int(9);

        if (empty($include)) {
            echo 'The --include option cannot be empty' . PHP_EOL . PHP_EOL;
            die();
        }
        if (empty($startCmd)) {
            echo 'The --cmd option cannot be empty' . PHP_EOL . PHP_EOL;
            die();
        }

        $include = Helper::split(',', $include);
        $exclude = Helper::split(',', $exclude);

        $process = new Process($startCmd, $signal);
        $process->start();

        $driver = new File($include, $exclude, $interval);
        $driver->callback(function () use ($process) {
            $process->isRunning();
        });
        $driver->run(function (string $file) use ($process) {
            echo 'File changed: ' . $file . PHP_EOL;
            echo 'File changed, reloading...' . PHP_EOL;
            $process->restart();
        });
    }
]);

$cmd->addOption(
    new Mix\Cli\Option([
        'names' => ['i', 'include'],
        'usage' => 'Contained paths, multiple paths separated by ","'
    ]),
    new Mix\Cli\Option([
        'names' => ['e', 'exclude'],
        'usage' => 'Excluded paths, multiple paths separated by ","'
    ]),
    new Mix\Cli\Option([
        'names' => ['t', 'interval'],
        'usage' => 'Monitoring file interval time in seconds'
    ]),
    new Mix\Cli\Option([
        'names' => ['c', 'cmd'],
        'usage' => 'Program start command'
    ]),
    new Mix\Cli\Option([
        'names' => ['s', 'signal'],
        'usage' => 'The signal sent to the child process. The default value is 9'
    ]),
);

Mix\Cli\Cli::addCommand($cmd)->run();
