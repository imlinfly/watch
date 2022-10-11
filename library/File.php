<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2022/10/21 21:50:42
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace library;

use Closure;
use Generator;

class File
{
    /**
     * Time of last change
     * @var int
     */
    protected int $lastTime;

    /**
     * Regular rules for exclude paths
     * @var string
     */
    protected string $excludeRule;

    /**
     * Callback events
     * @var Closure
     */
    protected Closure $callback;

    /**
     * File constructor.
     * @param array $include
     * @param array $exclude
     * @param float|int $interval
     */
    public function __construct(protected array $include, protected array $exclude, protected float|int $interval)
    {
        $this->lastTime = time();
        $this->interval = (int)($this->interval * 1000000);

        // Replace delimiters and wildcards
        $this->exclude = array_map(function ($rule) {
            return strtr(preg_quote($rule), [
                '\\\\' => '\\' . DIRECTORY_SEPARATOR,
                '/' => '\\' . DIRECTORY_SEPARATOR,
                '\\*' => '.*',
            ]);
        }, $this->exclude);

        $this->excludeRule = '/(' . implode('|', $this->exclude) . ')/';
    }

    /**
     * Traversing the folder list
     * @access public
     * @param string $path
     * @param Closure $filter
     * @return Generator
     */
    protected function findFiles(string $path, Closure $filter): Generator
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

    /**
     * Check of file changes
     * @access public
     * @return bool|string
     */
    public function isChange(): bool|string
    {
        // Loop check the list of folders
        foreach ($this->include as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $generator = $this->findFiles($path, function ($file) {
                // Filtering to exclude path
                if ('' !== $this->excludeRule && preg_match($this->excludeRule, $file)) {
                    return false;
                }
                return $file;
            });

            foreach ($generator as $file) {
                // Check file change time
                $fileTime = filemtime($file);
                if ($fileTime > $this->lastTime) {
                    // Update the last change time
                    $this->lastTime = $fileTime;
                    return $file;
                }
            }
        }

        return false;
    }

    /**
     * Run file watch
     * @access public
     * @param callable $callback
     * @return void
     */
    public function run(callable $callback): void
    {
        while (true) {
            if ($file = $this->isChange()) {
                // Trigger file change event
                $callback($file);
            }
            // Trigger callback
            ($this->callback)();
            usleep($this->interval);
        }
    }

    /**
     * Set callback events
     * @access public
     * @param Closure $callback
     * @return $this
     */
    public function callback(Closure $callback): static
    {
        $this->callback = $callback;
        return $this;
    }
}
