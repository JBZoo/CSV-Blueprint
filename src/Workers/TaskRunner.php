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

class TaskRunner
{
    private int $maxThreads;

    public function __construct(int $maxThreads = 4)
    {
        $this->maxThreads = $maxThreads;
    }

    public function runTasks(array $tasks): void
    {
        if (\extension_loaded('parallel')) {
            $this->runInParallel($tasks);
        } else {
            $this->runSequentially($tasks);
        }
    }

    private function runInParallel(array $tasks): void
    {
        $running = [];

        foreach ($tasks as $taskNumber) {
            if (\count($running) >= $this->maxThreads) {
                $index = \parallel\Future::select($running);
                $this->processResult($running[$index]->value());
                unset($running[$index]);
            }

            $runtime = new \parallel\Runtime();
            $running[] = $runtime->run($tasks[$taskNumber], [$taskNumber]);
        }

        foreach ($running as $future) {
            $this->processResult($future->value());
        }
    }

    private function runSequentially(array $tasks): void
    {
        foreach ($tasks as $taskNumber => $task) {
            $result = $task($taskNumber);
            $this->processResult($result);
        }
    }

    private function processResult($result): void
    {
        echo $result . "\n";
    }
}
