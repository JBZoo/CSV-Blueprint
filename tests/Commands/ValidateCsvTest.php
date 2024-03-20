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

use function JBZoo\PHPUnit\isNotEmpty;
use function JBZoo\PHPUnit\isSame;

final class ValidateCsvTest extends TestCase
{
    public function testValidateOneFilePositive(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => Tools::DEMO_CSV,
            'schema' => Tools::DEMO_YML_VALID,
        ]);

        $expected = $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_valid.yml
            Found CSV files: 1
            
            (1/1) OK: ./tests/fixtures/demo.csv
            
            Looks good!
            
            TXT;

        isSame(0, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateOneFileNegativeTable(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => Tools::DEMO_CSV_FULL,       // Full path
            'schema' => Tools::DEMO_YML_INVALID,    // Relative path
        ]);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 1
            
            Schema is invalid: ./tests/schemas/demo_invalid.yml
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            | Line  | id:Column        | Rule         | Message                                                              |
            +-------+------------------+--------------+----------------------------------------------------------------------+
            | undef | 2:Float          | is_float     | Value "Qwerty" is not a float number                                 |
            | undef | 4:Favorite color | allow_values | Value "123" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            
            (1/1) Invalid file: ./tests/fixtures/demo.csv
            +-------+------------------+------------------+----------------------- demo.csv ---------------------------------------------------------------------+
            | Line  | id:Column        | Rule             | Message                                                                                              |
            +-------+------------------+------------------+------------------------------------------------------------------------------------------------------+
            | undef |                  | filename_pattern | Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i"                     |
            | 1     |                  | csv.header       | Columns not found in CSV: "wrong_column_name"                                                        |
            | 6     | 0:Name           | length_min       | The length of the value "Carl" is 4, which is less than the expected "5"                             |
            | 11    | 0:Name           | length_min       | The length of the value "Lois" is 4, which is less than the expected "5"                             |
            | 1     | 1:City           | ag:is_unique     | Column has non-unique values. Unique: 9, total: 10                                                   |
            | 2     | 2:Float          | num_max          | The number of the value "4825.185", which is greater than the expected "4825.184"                    |
            | 6     | 3:Birthday       | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |       |                  |                  | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 8     | 3:Birthday       | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |       |                  |                  | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 9     | 3:Birthday       | date_max         | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than  |
            |       |                  |                  | the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                               |
            | 5     | 4:Favorite color | allow_values     | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]                                |
            +-------+------------------+------------------+----------------------- demo.csv ---------------------------------------------------------------------+
            
            
            Found 10 issues in CSV file.
            Found 2 issues in schema.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateManyFileNegativeTable(): void
    {
        $options = [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            Schema is invalid: ./tests/schemas/demo_invalid.yml
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            | Line  | id:Column        | Rule         | Message                                                              |
            +-------+------------------+--------------+----------------------------------------------------------------------+
            | undef | 2:Float          | is_float     | Value "Qwerty" is not a float number                                 |
            | undef | 4:Favorite color | allow_values | Value "123" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            +------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+
            | Line | id:Column        | Rule         | Message                                                               |
            +------+------------------+--------------+-----------------------------------------------------------------------+
            | 1    |                  | csv.header   | Columns not found in CSV: "wrong_column_name"                         |
            | 1    | 1:City           | ag:is_unique | Column has non-unique values. Unique: 1, total: 2                     |
            | 3    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            +------+------------+------------+---------------------------- demo-2.csv --------------------------------------------------------------+
            | Line | id:Column  | Rule       | Message                                                                                              |
            +------+------------+------------+------------------------------------------------------------------------------------------------------+
            | 1    |            | csv.header | Columns not found in CSV: "wrong_column_name"                                                        |
            | 2    | 0:Name     | length_min | The length of the value "Carl" is 4, which is less than the expected "5"                             |
            | 7    | 0:Name     | length_min | The length of the value "Lois" is 4, which is less than the expected "5"                             |
            | 2    | 3:Birthday | date_min   | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |            |            | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 4    | 3:Birthday | date_min   | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |            |            | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 5    | 3:Birthday | date_max   | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than  |
            |      |            |            | the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                               |
            +------+------------+------------+---------------------------- demo-2.csv --------------------------------------------------------------+
            
            (3/3) Invalid file: ./tests/fixtures/batch/sub/demo-3.csv
            +-------+-----------+------------------+--------------------- demo-3.csv -------------------------------------------------------------+
            | Line  | id:Column | Rule             | Message                                                                                      |
            +-------+-----------+------------------+----------------------------------------------------------------------------------------------+
            | undef |           | filename_pattern | Filename "./tests/fixtures/batch/sub/demo-3.csv" does not match pattern: "/demo-[12].csv$/i" |
            | 1     |           | csv.header       | Columns not found in CSV: "wrong_column_name"                                                |
            +-------+-----------+------------------+--------------------- demo-3.csv -------------------------------------------------------------+
            
            
            Found 11 issues in 3 out of 3 CSV files.
            Found 2 issues in schema.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateOneFileNegativeText(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/**/demo.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'text',
        ]);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 1
            
            Schema is invalid: ./tests/schemas/demo_invalid.yml
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            (1/1) Invalid file: ./tests/fixtures/demo.csv
            "filename_pattern". Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i".
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            "length_min" at line 6, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            "length_min" at line 11, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 9, total: 10.
            "num_max" at line 2, column "2:Float". The number of the value "4825.185", which is greater than the expected "4825.184".
            "date_min" at line 6, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_min" at line 8, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_max" at line 9, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            "allow_values" at line 5, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            
            Found 10 issues in CSV file.
            Found 2 issues in schema.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateManyFilesNegativeTextQuick(): void
    {
        $expectedQuick = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            Schema is invalid: ./tests/schemas/demo_invalid.yml
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
            
            (2/3) Skipped: ./tests/fixtures/batch/demo-2.csv
            (3/3) Skipped: ./tests/fixtures/batch/sub/demo-3.csv
            
            Found 2 issues in 1 out of 3 CSV files.
            Found 2 issues in schema.
            
            TXT;

        // No option (default behavior)
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'text',
            'Q'      => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Shortcut
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'text',
            'Q'      => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Shortcut 2
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'text',
            'quick'  => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Value - yes
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'text',
            'quick'  => 'yes',
        ]);

        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Value - no
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'text',
            'quick'  => 'no',
        ]);

        $expectedFull = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            Schema is invalid: ./tests/schemas/demo_invalid.yml
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
            "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            "length_min" at line 2, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            "length_min" at line 7, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            "date_min" at line 2, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_min" at line 4, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_max" at line 5, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            
            (3/3) Invalid file: ./tests/fixtures/batch/sub/demo-3.csv
            "filename_pattern". Filename "./tests/fixtures/batch/sub/demo-3.csv" does not match pattern: "/demo-[12].csv$/i".
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            
            
            Found 11 issues in 3 out of 3 CSV files.
            Found 2 issues in schema.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expectedFull, $actual);
    }

    public function testCreateValidateNegativeTeamcity(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'teamcity',
        ]);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            Schema is invalid: ./tests/schemas/demo_invalid.yml
            
            ##teamcity[testCount count='2' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo_invalid.yml' flowId='42']
            
            ##teamcity[testStarted name='is_float at column 2:Float' locationHint='php_qn://./tests/schemas/demo_invalid.yml' flowId='42']
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            ##teamcity[testFinished name='is_float at column 2:Float' flowId='42']
            
            ##teamcity[testStarted name='allow_values at column 4:Favorite color' locationHint='php_qn://./tests/schemas/demo_invalid.yml' flowId='42']
            "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            ##teamcity[testFinished name='allow_values at column 4:Favorite color' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo_invalid.yml' flowId='42']
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            
            ##teamcity[testCount count='3' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-1.csv' flowId='42']
            
            ##teamcity[testStarted name='csv.header at column' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            ##teamcity[testFinished name='csv.header at column' flowId='42']
            
            ##teamcity[testStarted name='ag:is_unique at column 1:City' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
            ##teamcity[testFinished name='ag:is_unique at column 1:City' flowId='42']
            
            ##teamcity[testStarted name='allow_values at column 4:Favorite color' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            ##teamcity[testFinished name='allow_values at column 4:Favorite color' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo-1.csv' flowId='42']
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            
            ##teamcity[testCount count='6' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-2.csv' flowId='42']
            
            ##teamcity[testStarted name='csv.header at column' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            ##teamcity[testFinished name='csv.header at column' flowId='42']
            
            ##teamcity[testStarted name='length_min at column 0:Name' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "length_min" at line 2, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            ##teamcity[testFinished name='length_min at column 0:Name' flowId='42']
            
            ##teamcity[testStarted name='length_min at column 0:Name' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "length_min" at line 7, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            ##teamcity[testFinished name='length_min at column 0:Name' flowId='42']
            
            ##teamcity[testStarted name='date_min at column 3:Birthday' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "date_min" at line 2, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            ##teamcity[testFinished name='date_min at column 3:Birthday' flowId='42']
            
            ##teamcity[testStarted name='date_min at column 3:Birthday' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "date_min" at line 4, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            ##teamcity[testFinished name='date_min at column 3:Birthday' flowId='42']
            
            ##teamcity[testStarted name='date_max at column 3:Birthday' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "date_max" at line 5, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            ##teamcity[testFinished name='date_max at column 3:Birthday' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo-2.csv' flowId='42']
            
            (3/3) Invalid file: ./tests/fixtures/batch/sub/demo-3.csv
            
            ##teamcity[testCount count='2' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-3.csv' flowId='42']
            
            ##teamcity[testStarted name='filename_pattern at column' locationHint='php_qn://./tests/fixtures/batch/sub/demo-3.csv' flowId='42']
            "filename_pattern". Filename "./tests/fixtures/batch/sub/demo-3.csv" does not match pattern: "/demo-[12].csv$/i".
            ##teamcity[testFinished name='filename_pattern at column' flowId='42']
            
            ##teamcity[testStarted name='csv.header at column' locationHint='php_qn://./tests/fixtures/batch/sub/demo-3.csv' flowId='42']
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            ##teamcity[testFinished name='csv.header at column' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo-3.csv' flowId='42']
            
            
            Found 11 issues in 3 out of 3 CSV files.
            Found 2 issues in schema.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testMultipleCsvOptions(): void
    {
        [$expected, $expectedCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
        ]);

        $actual = Tools::realExecution(
            'validate:csv ' . \implode(' ', [
                '--csv="./tests/fixtures/batch/sub/demo-3.csv"',
                '--csv="./tests/fixtures/batch/demo-1.csv"',
                '--csv="./tests/fixtures/batch/demo-2.csv"',
                '--csv="./tests/fixtures/batch/*.csv"',
                '--schema="' . Tools::DEMO_YML_INVALID . '"',
                '--mute-errors',
                '--stdout-only',
                '--no-ansi',
            ]),
            [],
            '',
        );

        isNotEmpty($expected);
        isNotEmpty($actual);
        isSame($expectedCode, 1);
        isSame($expected, $actual);
    }

    public function testInvalidSchema(): void
    {
        $options = [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => Tools::SCHEMA_INVALID,
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/invalid_schema.yml
            Found CSV files: 1
            
            Schema is invalid: ./tests/schemas/invalid_schema.yml
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
            
            (1/1) Invalid file: ./tests/fixtures/demo.csv
            +-------+------------+------------------+-------------------------- demo.csv ------------------------------------------------------------------+
            | Line  | id:Column  | Rule             | Message                                                                                              |
            +-------+------------+------------------+------------------------------------------------------------------------------------------------------+
            | undef |            | filename_pattern | Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i"                     |
            | 1     | 4:         | csv.header       | Property "name" is not defined in schema: "./tests/schemas/invalid_schema.yml"                       |
            | 1     |            | csv.header       | Columns not found in CSV: "4"                                                                        |
            | 6     | 0:Name     | length_min       | The length of the value "Carl" is 4, which is less than the expected "5"                             |
            | 11    | 0:Name     | length_min       | The length of the value "Lois" is 4, which is less than the expected "5"                             |
            | 1     | 1:City     | ag:is_unique     | Column has non-unique values. Unique: 9, total: 10                                                   |
            | 2     | 2:Float    | num_max          | The number of the value "4825.185", which is greater than the expected "4825.184"                    |
            | 2     | 3:Birthday | date_max         | The date of the value "2000-01-01" is parsed as "2000-01-01 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 2     | 3:Birthday | allow_values     | Value "2000-01-01" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 3     | 3:Birthday | date_max         | The date of the value "2000-12-01" is parsed as "2000-12-01 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 3     | 3:Birthday | allow_values     | Value "2000-12-01" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 4     | 3:Birthday | date_max         | The date of the value "2000-01-31" is parsed as "2000-01-31 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 4     | 3:Birthday | allow_values     | Value "2000-01-31" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 5     | 3:Birthday | date_max         | The date of the value "1998-02-28" is parsed as "1998-02-28 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 5     | 3:Birthday | allow_values     | Value "1998-02-28" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 6     | 3:Birthday | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |       |            |                  | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 6     | 3:Birthday | allow_values     | Value "1955-05-14" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 7     | 3:Birthday | date_max         | The date of the value "1989-05-15" is parsed as "1989-05-15 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 7     | 3:Birthday | allow_values     | Value "1989-05-15" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 8     | 3:Birthday | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |       |            |                  | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 8     | 3:Birthday | allow_values     | Value "1955-05-14" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 9     | 3:Birthday | date_max         | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 9     | 3:Birthday | allow_values     | Value "2010-07-20" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 10    | 3:Birthday | date_max         | The date of the value "1990-09-10" is parsed as "1990-09-10 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 10    | 3:Birthday | allow_values     | Value "1990-09-10" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            | 11    | 3:Birthday | date_max         | The date of the value "1988-08-24" is parsed as "1988-08-24 00:00:00 +00:00", which is greater than  |
            |       |            |                  | the expected "Can't parse date: 1"                                                                   |
            | 11    | 3:Birthday | allow_values     | Value "1988-08-24" is not allowed. Allowed values: ["red", "green", "Blue"]                          |
            +-------+------------+------------------+-------------------------- demo.csv ------------------------------------------------------------------+
            
            
            Found 27 issues in CSV file.
            Found 8 issues in schema.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testInvalidSchemaAndNoFoundCSV(): void
    {
        $options = [
            'csv'    => './tests/fixtures/no-found-file.csv',
            'schema' => Tools::SCHEMA_INVALID,
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/invalid_schema.yml
            Found CSV files: 0
            
            Schema is invalid: ./tests/schemas/invalid_schema.yml
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
            
            
            Found 8 issues in schema.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testSchemaNotFound(): void
    {
        $this->expectExceptionMessage('Schema file not found: invalid_schema_path.yml');
        Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/no-found-file.csv',
            'schema' => 'invalid_schema_path.yml',
        ]);
    }
}
