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

use HDSSolutions\Console\Parallel\ParallelWorker;
use JBZoo\CsvBlueprint\Schema;

final class ExampleWorker extends ParallelWorker
{
    public function __construct(
        private readonly string $schemaFilename,
    ) {
    }

    protected function process(string $schemaFilename): string
    {
        return (string)(new Schema($schemaFilename))->validate();
    }
}
