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

namespace JBZoo\CsvBlueprint\Tools;

final class RandomCsvGenerator
{
    private const MIN = 1;
    private const MAX = 10_000;

    public function __construct(
        private int $rows,
        private string $filePath,
        private array $columns,
    ) {
    }

    public function generateCsv(): void
    {
        $fileHandle = \fopen($this->filePath, 'w');
        if ($fileHandle === false) {
            throw new Exception("Can't open file: {$this->filePath}");
        }

        \fputcsv($fileHandle, $this->columns);

        for ($i = 0; $i < $this->rows; $i++) {
            $rowData = [];

            foreach (\array_keys($this->columns) as $columnIndex) {
                $rowData[$columnIndex] = \random_int(self::MIN, self::MAX);
            }

            \fputcsv($fileHandle, $rowData);
        }

        \fclose($fileHandle);
    }
}
