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

final class CsvGenerator
{
    public function __construct(
        private int $rows,
        private string $filePath,
        private array $columns,
    ) {
    }

    public function generateCsv(): void
    {
        $fileHandle = \fopen($this->filePath, 'w');

        \fputcsv($fileHandle, $this->columns);

        for ($i = 0; $i < $this->rows; $i++) {
            $rowData = [];

            foreach (\array_keys($this->columns) as $columnIndex) {
                $rowData[$columnIndex] = \random_int(1, 10000);
            }

            \fputcsv($fileHandle, $rowData);
        }

        \fclose($fileHandle);

        echo "CSV file created: {$this->filePath}.\n";
    }
}

(new CsvGenerator(
    1000,
    __DIR__ . '/random_data.csv',
    ['Column Name (header)', 'another_column', 'inherited_column_login', 'inherited_column_full_name'],
))->generateCsv();
