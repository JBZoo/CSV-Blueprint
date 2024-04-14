<?php

/**
 * JBZoo Toolbox - Csv-Blueprint.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @see        https://github.com/JBZoo/Csv-Blueprint
 */

declare(strict_types=1);

namespace JBZoo\CsvBlueprint\Workers;

use Fidry\CpuCoreCounter\CpuCoreCounter;
use JBZoo\CsvBlueprint\Utils;
use parallel\Runtime;

final class WorkerPool
{
    private const FALLBACK_CPU_COUNT = 1;
    private const POOL_MAINTENANCE_DELAY = 1_000; // Small delay to prevent overload CPU

    private static ?string $bootstrap = null;
    private int            $maxThreads;
    private \SplQueue      $tasksQueue;

    /** @var null[]|\parallel\Future[] */
    private array $runningTasks = [];

    public function __construct(int $maxThreads = 0)
    {
        $this->maxThreads = $maxThreads === 0 ? self::getCpuCount() : $maxThreads;
        $this->tasksQueue = new \SplQueue();
    }

    /**
     * Retrieves the maximum number of threads allowed.
     * @return int the maximum number of threads allowed
     */
    public function getMaxThreads(): int
    {
        return $this->maxThreads;
    }

    /**
     * Adds a task to the task queue.
     * @param string $key       the key associated with the task
     * @param string $taskClass the class name of the task to be added
     * @param array  $arguments the arguments to be passed to the task
     */
    public function addTask(string $key, string $taskClass, array $arguments = []): void
    {
        $this->tasksQueue->enqueue(new Worker($key, $taskClass, $arguments));
    }

    /**
     * Runs the method in either parallel or sequential mode, depending on the value of the isParallel property.
     * @param  null|\Closure $callback the optional callback function to be executed
     * @return array         returns an array of the results obtained from running the method
     */
    public function run(?\Closure $callback = null): array
    {
        return $this->isParallel() ? $this->runInParallel($callback) : $this->runSequentially($callback);
    }

    /**
     * Checks if the application is running in parallel mode.
     * @return bool returns true if the application is running in parallel mode, false otherwise
     */
    public function isParallel(): bool
    {
        return $this->getMaxThreads() > 1 && self::extLoaded();
    }

    /**
     * Checks if the 'parallel' extension is loaded.
     * @return bool returns true if the 'parallel' extension is loaded, false otherwise
     */
    public static function extLoaded(): bool
    {
        return \extension_loaded('parallel');
    }

    /**
     * Sets the bootstrap file for parallel execution.
     * @param string $autoloader the path to the autoloader file
     */
    public static function setBootstrap(string $autoloader): void
    {
        if (self::extLoaded() && self::$bootstrap === null) {
            $realpath = \realpath($autoloader);
            if ($realpath !== false) {
                self::$bootstrap = $realpath;
                // \parallel\bootstrap($autoloader); // Hm... Does it work?
            }
        }
    }

    /**
     * Retrieves the number of CPU cores on the current system.
     * Falls back to a default value if an error occurs during the retrieval.
     * @return int the number of CPU cores on the system
     */
    public static function getCpuCount(): int
    {
        try {
            return (new CpuCoreCounter())->getCount();
        } catch (\Throwable) {
            return self::FALLBACK_CPU_COUNT;
        }
    }

    private function runSequentially(?\Closure $callback = null): array
    {
        $results = [];

        while (!$this->tasksQueue->isEmpty()) {
            /** @var Worker $worker */
            $worker = $this->tasksQueue->dequeue();

            if ($callback !== null) {
                $callback($worker->getKey(), $worker->execute());
            } else {
                $results[$worker->getKey()] = $worker->execute();
            }
        }

        return $results;
    }

    private function runInParallel(?\Closure $callback = null): array
    {
        $results = [];

        while (!$this->tasksQueue->isEmpty() || \count($this->runningTasks) > 0) {
            $this->maintainTaskPool();

            foreach ($this->runningTasks as $index => $future) {
                if ($future !== null && $future->done()) {
                    if ($callback !== null) {
                        $callback($index, $future->value());
                    } else {
                        $results[$index] = $future->value();
                    }
                    unset($this->runningTasks[$index]);
                }
            }

            \usleep(self::POOL_MAINTENANCE_DELAY);
        }

        return $results;
    }

    private function maintainTaskPool(): void
    {
        $bootstrap = self::$bootstrap;
        if ($bootstrap === null) {
            throw new Exception('Bootstrap file is not set');
        }

        while (\count($this->runningTasks) < $this->maxThreads && !$this->tasksQueue->isEmpty()) {
            /** @var Worker $worker */
            $worker = $this->tasksQueue->dequeue();
            $runtime = new Runtime($bootstrap);
            $future = $runtime->run(
                static function (string $key, string $class, array $args, bool $debugMode): mixed {
                    Utils::init();
                    Utils::setDebugMode($debugMode);
                    return (new Worker($key, $class, $args))->execute();
                },
                [$worker->getKey(), $worker->getClass(), $worker->getArguments(), Utils::isDebugMode()],
            );

            $this->runningTasks[$worker->getKey()] = $future;
        }
    }
}
