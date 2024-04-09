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

namespace JBZoo\PHPUnit\Workers;

use JBZoo\CsvBlueprint\Workers\WorkerPool;
use JBZoo\PHPUnit\TestCase;

use function JBZoo\PHPUnit\isFalse;
use function JBZoo\PHPUnit\isSame;
use function JBZoo\PHPUnit\isTrue;
use function JBZoo\PHPUnit\skip;

final class TaskRunnerTest extends TestCase
{
    public function testIsParallel(): void
    {
        $runner = new WorkerPool();
        isSame(\extension_loaded('parallel'), $runner->isParallel());

        $runner = new WorkerPool(1);
        isFalse($runner->isParallel());
    }

    public function testExecuteSequentially(): void
    {
        $runner = new WorkerPool(1);
        $runner->addTask('q', TestTask::class, [1]);
        $runner->addTask('qq', TestTask::class, [2]);
        $runner->addTask('qqq', TestTask::class, [3]);

        $startTime = \microtime(true);
        isSame(['q' => 1, 'qq' => 2, 'qqq' => 3], $runner->run());
        $time = \microtime(true) - $startTime;

        isTrue($time >= TestTask::DELAY * 3, (string)$time);
    }

    public function testExecuteParallel(): void
    {
        self::onlyParallel();

        $runner = new WorkerPool();
        isTrue($runner->getMaxThreads() > 1);
        $runner->addTask('q', TestTask::class, [1]);
        $runner->addTask('qq', TestTask::class, [2]);
        $runner->addTask('qqq', TestTask::class, [3]);

        $startTime = \microtime(true);
        isSame(['q' => 1, 'qq' => 2, 'qqq' => 3], $runner->run());
        $time = \microtime(true) - $startTime;

        isTrue($time < TestTask::DELAY * 3, (string)$time);
    }

    private static function onlyParallel(): void
    {
        if (!\extension_loaded('parallel')) {
            skip('The parallel extension is not available.');
        }
    }
}
