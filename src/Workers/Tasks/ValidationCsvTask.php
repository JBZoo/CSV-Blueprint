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

namespace JBZoo\CsvBlueprint\Workers\Tasks;

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;

final class ValidationCsvTask extends AbstractTask
{
    public function __construct(
        private readonly string $csvFilename,
        private readonly string $schemaFilename,
        private readonly bool $isQuickMode = false,
    ) {
    }

    /**
     * Processes the given CSV file and returns an ErrorSuite object.
     */
    public function process(): ErrorSuite
    {
        return (new CsvFile($this->csvFilename, $this->schemaFilename))->validate($this->isQuickMode);
    }
}
