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

use JBZoo\CsvBlueprint\Workers\Tasks\AbstractTask;

final class Worker
{
    public function __construct(
        private readonly string $key,
        private readonly string $className,
        private readonly array $arguments = [],
    ) {
    }

    /**
     * Returns the key value.
     * @return string the key value
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Executes the task.
     * @return mixed                     the result of the task execution
     * @throws \InvalidArgumentException if the class specified by `$className` is not found or is not
     *                                   an instance of AbstractTask
     */
    public function execute(): mixed
    {
        $className = $this->className;
        if (\class_exists($className) === false) {
            throw new \InvalidArgumentException("Class '{$className}' not found");
        }

        $task = new $className(...$this->arguments);
        if (!$task instanceof AbstractTask) {
            throw new \InvalidArgumentException("Class '{$className}' is not allowed");
        }

        return $task->process();
    }

    /**
     * Returns the name of the class.
     * @return string the name of the class
     */
    public function getClass(): string
    {
        return $this->className;
    }

    /**
     * Returns the arguments.
     * @return array the worker arguments as is
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
