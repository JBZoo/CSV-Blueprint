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

use JBZoo\CsvBlueprint\Tools\RandomCsvGenerator;
use JBZoo\Utils\Cli;

require_once __DIR__ . '/../vendor/autoload.php';

(new RandomCsvGenerator(
    1000,
    __DIR__ . '/random_data.csv',
    ['Column Name (header)', 'another_column', 'inherited_column_login', 'inherited_column_full_name'],
))->generateCsv();

Cli::out('Random CSV file with 1000 lines created: ' . __DIR__ . '/random_data.csv');
