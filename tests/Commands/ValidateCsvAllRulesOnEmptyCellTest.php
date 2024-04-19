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

namespace JBZoo\PHPUnit\Commands;

use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

final class ValidateCsvAllRulesOnEmptyCellTest extends TestCase
{
    public function test(): void
    {
        $optionsAsString = Tools::arrayToOptions([
            'csv'    => './tests/fixtures/empty_cells.csv',
            'schema' => './schema-examples/full.yml',
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1 (Apply All)
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
              OK ./schema-examples/full.yml
            
            CSV file validation: 1
            Schema: ./schema-examples/full.yml
              36 issues in ./tests/fixtures/empty_cells.csv; Size: 123.34 MB
                +------+------------------------------+----------------------------+------------------------------------------------------------------------------------------------------+
                | Line | id:Column                    | Rule                       | Message                                                                                              |
                +------+------------------------------+----------------------------+------------------------------------------------------------------------------------------------------+
                |    2 | 0:Column Name (header)       | not_empty                  | Value is empty                                                                                       |
                |    3 | 0:Column Name (header)       | not_empty                  | Value is empty                                                                                       |
                |    1 | 0:Column Name (header)       | ag:is_unique               | Column has non-unique values. Unique: 1, total: 2                                                    |
                |    1 | 0:Column Name (header)       | ag:first_num_min           | The first value in the column is "0", which is less than the expected "1"                            |
                |    1 | 0:Column Name (header)       | ag:first_num_greater       | The first value in the column is "0", which is less and not equal than the expected "2"              |
                |    1 | 0:Column Name (header)       | ag:first_num               | The first value in the column is "0", which is not equal than the expected "7"                       |
                |    1 | 0:Column Name (header)       | ag:first                   | The first value in the column is "", which is not equal than the expected "Expected"                 |
                |    1 | 0:Column Name (header)       | ag:nth_num_min             | The column does not have a line 42, so the value cannot be checked.                                  |
                |    1 | 0:Column Name (header)       | ag:nth_num_greater         | The column does not have a line 42, so the value cannot be checked.                                  |
                |    1 | 0:Column Name (header)       | ag:nth_num_not             | The column does not have a line 42, so the value cannot be checked.                                  |
                |    1 | 0:Column Name (header)       | ag:nth_num                 | The column does not have a line 42, so the value cannot be checked.                                  |
                |    1 | 0:Column Name (header)       | ag:nth_num_less            | The column does not have a line 42, so the value cannot be checked.                                  |
                |    1 | 0:Column Name (header)       | ag:nth_num_max             | The column does not have a line 42, so the value cannot be checked.                                  |
                |    1 | 0:Column Name (header)       | ag:nth                     | The value on line 2 in the column is "", which is not equal than the expected "Expected"             |
                |    1 | 0:Column Name (header)       | ag:last_num_min            | The last value in the column is "0", which is less than the expected "1"                             |
                |    1 | 0:Column Name (header)       | ag:last_num_greater        | The last value in the column is "0", which is less and not equal than the expected "2"               |
                |    1 | 0:Column Name (header)       | ag:last_num                | The last value in the column is "0", which is not equal than the expected "7"                        |
                |    1 | 0:Column Name (header)       | ag:last                    | The last value in the column is "", which is not equal than the expected "Expected"                  |
                |    1 | 0:Column Name (header)       | ag:count_greater           | The number of rows in the column is "2", which is less and not equal than the expected "2"           |
                |    1 | 0:Column Name (header)       | ag:count                   | The number of rows in the column is "2", which is not equal than the expected "7"                    |
                |    1 | 0:Column Name (header)       | ag:count_empty_greater     | The number of empty rows in the column is "2", which is less and not equal than the expected "2"     |
                |    1 | 0:Column Name (header)       | ag:count_empty             | The number of empty rows in the column is "2", which is not equal than the expected "7"              |
                |    1 | 0:Column Name (header)       | ag:count_not_empty_min     | The number of not empty rows in the column is "0", which is less than the expected "1"               |
                |    1 | 0:Column Name (header)       | ag:count_not_empty_greater | The number of not empty rows in the column is "0", which is less and not equal than the expected "2" |
                |    1 | 0:Column Name (header)       | ag:count_not_empty_not     | The number of not empty rows in the column is "0", which is equal than the not expected "0"          |
                |    1 | 0:Column Name (header)       | ag:count_not_empty         | The number of not empty rows in the column is "0", which is not equal than the expected "7"          |
                |    1 | 0:Column Name (header)       | ag:count_distinct_greater  | The number of unique values in the column is "1", which is less and not equal than the expected "2"  |
                |    1 | 0:Column Name (header)       | ag:count_distinct          | The number of unique values in the column is "1", which is not equal than the expected "7"           |
                |    2 | 1:another_column             | not_empty                  | Value is empty                                                                                       |
                |    3 | 1:another_column             | not_empty                  | Value is empty                                                                                       |
                |    2 | 2:inherited_column_login     | not_empty                  | Value is empty                                                                                       |
                |    3 | 2:inherited_column_login     | not_empty                  | Value is empty                                                                                       |
                |    1 | 2:inherited_column_login     | ag:is_unique               | Column has non-unique values. Unique: 1, total: 2                                                    |
                |    2 | 3:inherited_column_full_name | not_empty                  | Value is empty                                                                                       |
                |    3 | 3:inherited_column_full_name | not_empty                  | Value is empty                                                                                       |
                |    1 | 3:inherited_column_full_name | ag:is_unique               | Column has non-unique values. Unique: 1, total: 2                                                    |
                +------+------------------------------+----------------------------+------------------------------------------------------------------------------------------------------+
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              Found 36 issues in 1 out of 1 CSV files.
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }
}
