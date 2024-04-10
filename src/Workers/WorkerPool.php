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
use parallel\Runtime;

final class WorkerPool
{
    private const FALLBACK_CPU_COUNT = 1;
    private const POOL_MAINTENANCE_DELAY = 10_000; // Small delay to prevent overload CPU

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

    public function getMaxThreads(): int
    {
        return $this->maxThreads;
    }

    public function addTask(string $key, string $taskClass, array $arguments = []): void
    {
        $this->tasksQueue->enqueue(new Worker($key, $taskClass, $arguments));
    }

    public function run(): array
    {
        return $this->isParallel() ? $this->runInParallel() : $this->runSequentially();
    }

    public function isParallel(): bool
    {
        return $this->getMaxThreads() > 1 && self::extLoaded();
    }

    public static function extLoaded(): bool
    {
        return \extension_loaded('parallel');
    }

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

    public static function getCpuCount(): int
    {
        try {
            return (new CpuCoreCounter())->getCount();
        } catch (\Throwable) {
            return self::FALLBACK_CPU_COUNT;
        }
    }

    private function runSequentially(): array
    {
        $results = [];

        while (!$this->tasksQueue->isEmpty()) {
            /** @var Worker $worker */
            $worker = $this->tasksQueue->dequeue();
            $results[$worker->getKey()] = $worker->execute();
        }

        return $results;
    }

    private function runInParallel(): array
    {
        $results = [];

        while (!$this->tasksQueue->isEmpty() || \count($this->runningTasks) > 0) {
            $this->maintainTaskPool();

            foreach ($this->runningTasks as $index => $future) {
                if ($future !== null && $future->done()) {
                    $results[$index] = $future->value();
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
                static fn (string $key, string $class, array $args): mixed => (new Worker($key, $class, $args))
                    ->execute(),
                [$worker->getKey(), $worker->getClass(), $worker->getArguments()],
            );

            $this->runningTasks[$worker->getKey()] = $future;
        }
    }
}
