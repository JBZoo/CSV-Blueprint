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

final class ValidateCsvBatchSchemaTest extends TestCase
{
    public function testMultiSchemaDiscovery(): void
    {
        $optionsAsString = Tools::arrayToOptionString([
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => [
                Tools::SCHEMA_INVALID,
                Tools::DEMO_YML_VALID,
                Tools::DEMO_YML_INVALID,
            ],
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 3
            Found CSV files : 1
            Pairs by pattern: 2
            
            Check schema syntax: 3
            (1/3) Schema: ./tests/schemas/demo_invalid.yml
            (1/3) Issues: 2
            +-------+------------------+------------- tests/schemas/demo_invalid.yml ----------------------------------------+
            | Line  | id:Column        | Rule         | Message                                                              |
            +-------+------------------+--------------+----------------------------------------------------------------------+
            | undef | 2:Float          | is_float     | Value "Qwerty" is not a float number                                 |
            | undef | 4:Favorite color | allow_values | Value "123" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +-------+------------------+------------- tests/schemas/demo_invalid.yml ----------------------------------------+
            
            (2/3) OK: ./tests/schemas/demo_valid.yml
            (3/3) Schema: ./tests/schemas/invalid_schema.yml
            (3/3) Issues: 8
            +-------+------------+--------+---- tests/schemas/invalid_schema.yml -----------------------------------+
            | Line  | id:Column  | Rule   | Message                                                                 |
            +-------+------------+--------+-------------------------------------------------------------------------+
            | undef | meta       | schema | Unknown key: .unknow_root_option                                        |
            | undef | meta       | schema | Unknown key: .csv.unknow_csv_param                                      |
            | undef | 0:Name     | schema | Unknown key: .columns.0.rules.unknow_rule                               |
            | undef | 1:City     | schema | Unknown key: .columns.1.unknow_colum_option                             |
            | undef | 3:Birthday | schema | Expected type "string", actual "boolean" in .columns.3.rules.date_max   |
            | undef | 4:         | schema | The key "name" must be non-empty because the option "csv.header" = true |
            | undef | 4:         | schema | Expected type "boolean", actual "string" in .columns.4.rules.not_empty  |
            | undef | 4:         | schema | Expected type "array", actual "string" in .columns.4.rules.allow_values |
            +-------+------------+--------+---- tests/schemas/invalid_schema.yml -----------------------------------+
            
            
            CSV file validation: 2
            (1/2) Schema: ./tests/schemas/demo_invalid.yml
            (1/2) CSV   : ./tests/fixtures/demo.csv; Size: 123.34 MB
            (1/2) Issues: 10
            +------+------------------+---------------------+-------------- tests/fixtures/demo.csv ---------------------------------------------------------------+
            | Line | id:Column        | Rule                | Message                                                                                              |
            +------+------------------+---------------------+------------------------------------------------------------------------------------------------------+
            | 1    |                  | allow_extra_columns | Column(s) not found in CSV: "wrong_column_name"                                                      |
            | 6    | 0:Name           | length_min          | The length of the value "Carl" is 4, which is less than the expected "5"                             |
            | 11   | 0:Name           | length_min          | The length of the value "Lois" is 4, which is less than the expected "5"                             |
            | 1    | 1:City           | ag:is_unique        | Column has non-unique values. Unique: 9, total: 10                                                   |
            | 2    | 2:Float          | num_max             | The value "4825.185" is greater than the expected "4825.184"                                         |
            | 1    | 2:Float          | ag:nth_num          | The N-th value in the column is "74", which is not equal than the expected "0.001"                   |
            | 6    | 3:Birthday       | date_min            | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |                  |                     | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 8    | 3:Birthday       | date_min            | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |                  |                     | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 9    | 3:Birthday       | date_max            | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than  |
            |      |                  |                     | the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                               |
            | 5    | 4:Favorite color | allow_values        | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]                                |
            +------+------------------+---------------------+-------------- tests/fixtures/demo.csv ---------------------------------------------------------------+
            
            (2/2) Schema: ./tests/schemas/demo_valid.yml
            (2/2) CSV   : ./tests/fixtures/demo.csv; Size: 123.34 MB
            (2/2) OK
            
            Summary:
              2 pairs (schema to csv) were found based on `filename_pattern`.
              Found 10 issues in 3 schemas.
              Found 10 issues in 1 out of 1 CSV files.
              Not used schemas:
                - ./tests/schemas/invalid_schema.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testNoPattern(): void
    {
        $optionsAsString = Tools::arrayToOptionString([
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => [
                Tools::DEMO_YML_VALID,
                './tests/schemas/demo_invalid_no_pattern.yml',
            ],
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 2
            Found CSV files : 1
            Pairs by pattern: 2
            
            Check schema syntax: 2
            (1/2) OK: ./tests/schemas/demo_invalid_no_pattern.yml
            (2/2) OK: ./tests/schemas/demo_valid.yml
            
            CSV file validation: 2
            (1/2) Schema: ./tests/schemas/demo_invalid_no_pattern.yml
            (1/2) CSV   : ./tests/fixtures/demo.csv; Size: 123.34 MB
            (1/2) Issues: 2
            +------+-----------+-------- tests/fixtures/demo.csv ----------------------------+
            | Line | id:Column | Rule    | Message                                           |
            +------+-----------+---------+---------------------------------------------------+
            | 4    | 2:Float   | num_min | The value "-177.90" is less than the expected "0" |
            | 11   | 2:Float   | num_min | The value "-200.1" is less than the expected "0"  |
            +------+-----------+-------- tests/fixtures/demo.csv ----------------------------+
            
            (2/2) Schema: ./tests/schemas/demo_valid.yml
            (2/2) CSV   : ./tests/fixtures/demo.csv; Size: 123.34 MB
            (2/2) OK
            
            Summary:
              2 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 2 schemas.
              Found 2 issues in 1 out of 1 CSV files.
              Schemas have no filename_pattern and are applied to all CSV files found:
                - ./tests/schemas/demo_invalid_no_pattern.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }
}
