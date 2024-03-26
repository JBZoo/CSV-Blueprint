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
use JBZoo\Utils\Cli;
use Symfony\Component\Console\Input\StringInput;

use function JBZoo\PHPUnit\isSame;

final class ValidateCsvBasicTest extends TestCase
{
    public function testValidateOneCsvPositive(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => Tools::DEMO_CSV,
            'schema' => Tools::DEMO_YML_VALID,
        ]);

        $expected = $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
            (1/1) OK: ./tests/schemas/demo_valid.yml
            
            CSV file validation: 1
            (1/1) Schema: ./tests/schemas/demo_valid.yml
            (1/1) CSV   : ./tests/fixtures/demo.csv
            (1/1) OK
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              No issues in 1 CSV files.
              Looks good!
            
            
            TXT;

        isSame(0, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateOneCsvNegative(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => Tools::DEMO_INVALID_CSV,
            'schema' => Tools::DEMO_YML_VALID,
        ]);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
            (1/1) OK: ./tests/schemas/demo_valid.yml
            
            CSV file validation: 1
            (1/1) Schema: ./tests/schemas/demo_valid.yml
            (1/1) CSV   : ./tests/fixtures/demo_invalid.csv
            (1/1) Issues: 2
            +------+------------------+--------------+-------------- demo_invalid.csv --------------------------------------------------------+
            | Line | id:Column        | Rule         | Message                                                                                |
            +------+------------------+--------------+----------------------------------------------------------------------------------------+
            | 6    | 0:Name           | length_max   | The length of the value "Long-long-name" is 14, which is greater than the expected "7" |
            | 11   | 4:Favorite color | allow_values | Value "YELLOW" is not allowed. Allowed values: ["red", "green", "blue"]                |
            +------+------------------+--------------+-------------- demo_invalid.csv --------------------------------------------------------+
            
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              Found 2 issues in 1 out of 1 CSV files.
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateOneCsvWithInvalidSchemaNegative(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => Tools::DEMO_CSV,
            'schema' => Tools::DEMO_YML_INVALID,
        ]);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) Issues: 2
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            | Line  | id:Column        | Rule         | Message                                                              |
            +-------+------------------+--------------+----------------------------------------------------------------------+
            | undef | 2:Float          | is_float     | Value "Qwerty" is not a float number                                 |
            | undef | 4:Favorite color | allow_values | Value "123" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            
            
            CSV file validation: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) CSV   : ./tests/fixtures/demo.csv
            (1/1) Issues: 10
            +------+------------------+--------------+------------------------- demo.csv -------------------------------------------------------------------+
            | Line | id:Column        | Rule         | Message                                                                                              |
            +------+------------------+--------------+------------------------------------------------------------------------------------------------------+
            | 1    |                  | csv.header   | Columns not found in CSV: "wrong_column_name"                                                        |
            | 6    | 0:Name           | length_min   | The length of the value "Carl" is 4, which is less than the expected "5"                             |
            | 11   | 0:Name           | length_min   | The length of the value "Lois" is 4, which is less than the expected "5"                             |
            | 1    | 1:City           | ag:is_unique | Column has non-unique values. Unique: 9, total: 10                                                   |
            | 2    | 2:Float          | num_max      | The value "4825.185" is greater than the expected "4825.184"                                         |
            | 1    | 2:Float          | ag:nth_num   | The N-th value in the column is "74", which is not equal than the expected "0.001"                   |
            | 6    | 3:Birthday       | date_min     | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |                  |              | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 8    | 3:Birthday       | date_min     | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |                  |              | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 9    | 3:Birthday       | date_max     | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than  |
            |      |                  |              | the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                               |
            | 5    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]                                |
            +------+------------------+--------------+------------------------- demo.csv -------------------------------------------------------------------+
            
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              Found 2 issues in 1 schemas.
              Found 10 issues in 1 out of 1 CSV files.
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    // ###################################################################################################################

    public function testInvalidSchemaNotMatched(): void
    {
        $options = [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => Tools::SCHEMA_INVALID,
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 0
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/invalid_schema.yml
            (1/1) Issues: 8
            +-------+------------+--------+----------- invalid_schema.yml ------------------------------------------+
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
            +-------+------------+--------+----------- invalid_schema.yml ------------------------------------------+
            
            
            CSV file validation: 0
            
            Summary:
              0 pairs (schema to csv) were found based on `filename_pattern`.
              Found 8 issues in 1 schemas.
              No issues in 1 CSV files.
              No schema was applied to the CSV files (filename_pattern didn't match):
                - ./tests/fixtures/demo.csv
              Not used schemas:
                - ./tests/schemas/invalid_schema.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testInvalidSchemaAndNotFoundCSV(): void
    {
        $options = [
            'csv'    => './tests/fixtures/no-found-file.csv',
            'schema' => Tools::SCHEMA_INVALID,
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 0
            Pairs by pattern: 0
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/invalid_schema.yml
            (1/1) Issues: 8
            +-------+------------+--------+----------- invalid_schema.yml ------------------------------------------+
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
            +-------+------------+--------+----------- invalid_schema.yml ------------------------------------------+
            
            
            CSV file validation: 0
            
            Summary:
              0 pairs (schema to csv) were found based on `filename_pattern`.
              Found 8 issues in 1 schemas.
              No issues in 0 CSV files.
              Not used schemas:
                - ./tests/schemas/invalid_schema.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateOneCsvNoHeaderNegative(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => Tools::DEMO_CSV,
            'schema' => './tests/schemas/simple_no_header.yml',
        ]);

        $expected = $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
            (1/1) OK: ./tests/schemas/simple_no_header.yml
            
            CSV file validation: 1
            (1/1) Schema: ./tests/schemas/simple_no_header.yml
            (1/1) CSV   : ./tests/fixtures/demo.csv
            (1/1) Issues: 2
            +------+-----------+---------- demo.csv -----------------------------+
            | Line | id:Column | Rule             | Message                      |
            +------+-----------+------------------+------------------------------+
            | 2    | 0:        | not_allow_values | Value "Clyde" is not allowed |
            | 5    | 2:        | not_allow_values | Value "74" is not allowed    |
            +------+-----------+---------- demo.csv -----------------------------+
            
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              Found 2 issues in 1 out of 1 CSV files.
              Schemas have no filename_pattern and are applied to all CSV files found:
                - ./tests/schemas/simple_no_header.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testSchemaNotFound(): void
    {
        $this->expectExceptionMessage('Schema file(s) not found: invalid_schema_path.yml');
        Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/no-found-file.csv',
            'schema' => 'invalid_schema_path.yml',
        ]);
    }
}
