<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2022/10/21 21:50:42
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace library;

use Generator;

class File
{
    protected int $lastTime;
    protected string $excludeRule;
    protected \Closure $callback;

    public function __construct(protected array $include, protected array $exclude, protected float|int $interval)
    {
        $this->lastTime = time();
        $this->interval = (int)($this->interval * 1000000);

        $this->exclude = array_map(function ($rule) {
            return strtr(preg_quote($rule), [
                '\\\\' => '\\' . DIRECTORY_SEPARATOR,
                '/' => '\\' . DIRECTORY_SEPARATOR,
                '\\*' => '.*',
            ]);
        }, $this->exclude);

        $this->excludeRule = '/(' . implode('|', $this->exclude) . ')/';
    }

    protected function findFiles(string $path, \Closure $filter): Generator
    {
        $path = realpath($path);

        if (!$path) {
            return;
        }

        $dh = opendir($path);
        while ($file = readdir($dh)) {
            if ('.' !== $file && '..' !== $file) {
                $pathname = $path . DIRECTORY_SEPARATOR . $file;
                if (is_file($pathname)) {
                    ($result = $filter($pathname, $path)) && yield $result;
                } else {
                    yield from $this->findFiles($pathname, $filter);
                }
            }
        }
        closedir($dh);
    }

    public function isChange(): bool|string
    {
        foreach ($this->include as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $generator = $this->findFiles($path, function ($file) {
                if ('' !== $this->excludeRule && preg_match($this->excludeRule, $file)) {
                    return false;
                }
                return $file;
            });

            foreach ($generator as $file) {
                $fileTime = filemtime($file);
                if ($fileTime > $this->lastTime) {
                    $this->lastTime = $fileTime;
                    return $file;
                }
            }
        }

        return false;
    }

    public function run(callable $callback): void
    {
        while (true) {
            if ($file = $this->isChange()) {
                $callback($file);
            }
            ($this->callback)();
            usleep($this->interval);
        }
    }

    public function callback(\Closure $callback): static
    {
        $this->callback = $callback;
        return $this;
    }
}
