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

$rows = 1000;

$filePath = __DIR__ . '/random_data.csv';

$fileHandle = \fopen($filePath, 'w');

if ($fileHandle === false) {
    exit('Error opening the file');
}

$columns = ['Column Name (header)', 'another_column', 'inherited_column_login', 'inherited_column_full_name'];
\fputcsv($fileHandle, $columns);

for ($i = 0; $i < $rows; $i++) {
    $rowData = [];

    for ($j = 0; $j < \count($columns); $j++) {
        $rowData[] = \random_int(1, 10000);
    }

    \fputcsv($fileHandle, $rowData);
}

\fclose($fileHandle);

echo "CSV file created: {$filePath}.\n";
