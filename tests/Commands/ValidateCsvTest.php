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
            'csv'    => "{$rootPath}/tests/fixtures/demo.csv",
            'schema' => "{$rootPath}/tests/schemas/demo_valid.yml",
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
            'csv'    => "{$rootPath}/tests/fixtures/demo.csv", // Full path
            'schema' => './tests/schemas/demo_invalid.yml',    // Relative path
        ]);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 1
            
            (1/1) Invalid file: ./tests/fixtures/demo.csv
            +------+------------------+------------------+--------------------- demo.csv -------------------------------------------------------------------+
            | Line | id:Column        | Rule             | Message                                                                                          |
            +------+------------------+------------------+--------------------------------------------------------------------------------------------------+
            | 1    |                  | filename_pattern | Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i"                 |
            | 6    | 0:Name           | length_min       | The length of the value "Carl" is 4, which is less than the expected "5"                         |
            | 11   | 0:Name           | length_min       | The length of the value "Lois" is 4, which is less than the expected "5"                         |
            | 1    | 1:City           | ag:is_unique     | Column has non-unique values. Unique: 9, total: 10                                               |
            | 5    | 2:Float          | num_max          | The number of the value "74605.944", which is greater than the expected "74605"                  |
            | 6    | 3:Birthday       | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than |
            |      |                  |                  | the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                           |
            | 8    | 3:Birthday       | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than |
            |      |                  |                  | the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                           |
            | 9    | 3:Birthday       | date_max         | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater   |
            |      |                  |                  | than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                      |
            | 5    | 4:Favorite color | allow_values     | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]                            |
            +------+------------------+------------------+--------------------- demo.csv -------------------------------------------------------------------+
            
            
            Found 9 issues in CSV file.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateManyFileNegativeTable(): void
    {
        $options = [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            +------+------------------+--------------+-------------- demo-1.csv -------------------------------------------------------+
            | Line | id:Column        | Rule         | Message                                                                         |
            +------+------------------+--------------+---------------------------------------------------------------------------------+
            | 1    | 1:City           | ag:is_unique | Column has non-unique values. Unique: 1, total: 2                               |
            | 3    | 2:Float          | num_max      | The number of the value "74605.944", which is greater than the expected "74605" |
            | 3    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]           |
            +------+------------------+--------------+-------------- demo-1.csv -------------------------------------------------------+
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            +------+------------+------------+-------------------------- demo-2.csv ------------------------------------------------------------+
            | Line | id:Column  | Rule       | Message                                                                                          |
            +------+------------+------------+--------------------------------------------------------------------------------------------------+
            | 2    | 0:Name     | length_min | The length of the value "Carl" is 4, which is less than the expected "5"                         |
            | 7    | 0:Name     | length_min | The length of the value "Lois" is 4, which is less than the expected "5"                         |
            | 2    | 3:Birthday | date_min   | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than |
            |      |            |            | the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                           |
            | 4    | 3:Birthday | date_min   | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than |
            |      |            |            | the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                           |
            | 5    | 3:Birthday | date_max   | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater   |
            |      |            |            | than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                      |
            +------+------------+------------+-------------------------- demo-2.csv ------------------------------------------------------------+
            
            (3/3) Invalid file: ./tests/fixtures/batch/sub/demo-3.csv
            +------+-----------+------------------+---------------------- demo-3.csv ------------------------------------------------------------+
            | Line | id:Column | Rule             | Message                                                                                      |
            +------+-----------+------------------+----------------------------------------------------------------------------------------------+
            | 1    |           | filename_pattern | Filename "./tests/fixtures/batch/sub/demo-3.csv" does not match pattern: "/demo-[12].csv$/i" |
            +------+-----------+------------------+---------------------- demo-3.csv ------------------------------------------------------------+
            
            
            Found 9 issues in 3 out of 3 CSV files.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateOneFileNegativeText(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/**/demo.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
        ]);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 1
            
            (1/1) Invalid file: ./tests/fixtures/demo.csv
            "filename_pattern" at line 1, column "". Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i".
            "length_min" at line 6, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            "length_min" at line 11, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 9, total: 10.
            "num_max" at line 5, column "2:Float". The number of the value "74605.944", which is greater than the expected "74605".
            "date_min" at line 6, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_min" at line 8, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_max" at line 9, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            "allow_values" at line 5, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            
            Found 9 issues in CSV file.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateManyFilesNegativeTextQuick(): void
    {
        $expectedQuick = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
            
            (2/3) Skipped: ./tests/fixtures/batch/demo-2.csv
            (3/3) Skipped: ./tests/fixtures/batch/sub/demo-3.csv
            
            Found 1 issues in 1 out of 3 CSV files.
            
            TXT;

        // No option (default behavior)
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'Q'      => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Shortcut
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'Q'      => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Shortcut 2
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'quick'  => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Value - yes
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'quick'  => 'yes',
        ]);

        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Value - no
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'quick'  => 'no',
        ]);

        $expectedFull = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
            "num_max" at line 3, column "2:Float". The number of the value "74605.944", which is greater than the expected "74605".
            "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            "length_min" at line 2, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            "length_min" at line 7, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            "date_min" at line 2, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_min" at line 4, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_max" at line 5, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            
            (3/3) Invalid file: ./tests/fixtures/batch/sub/demo-3.csv
            "filename_pattern" at line 1, column "". Filename "./tests/fixtures/batch/sub/demo-3.csv" does not match pattern: "/demo-[12].csv$/i".
            
            
            Found 9 issues in 3 out of 3 CSV files.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expectedFull, $actual);
    }

    public function testCreateValidateNegativeTeamcity(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'teamcity',
        ]);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            
            ##teamcity[testCount count='3' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-1.csv' flowId='42']
            
            ##teamcity[testStarted name='ag:is_unique at column 1:City' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
            ##teamcity[testFinished name='ag:is_unique at column 1:City' flowId='42']
            
            ##teamcity[testStarted name='num_max at column 2:Float' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "num_max" at line 3, column "2:Float". The number of the value "74605.944", which is greater than the expected "74605".
            ##teamcity[testFinished name='num_max at column 2:Float' flowId='42']
            
            ##teamcity[testStarted name='allow_values at column 4:Favorite color' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            ##teamcity[testFinished name='allow_values at column 4:Favorite color' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo-1.csv' flowId='42']
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            
            ##teamcity[testCount count='5' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-2.csv' flowId='42']
            
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
            
            ##teamcity[testCount count='1' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-3.csv' flowId='42']
            
            ##teamcity[testStarted name='filename_pattern at column' locationHint='php_qn://./tests/fixtures/batch/sub/demo-3.csv' flowId='42']
            "filename_pattern" at line 1, column "". Filename "./tests/fixtures/batch/sub/demo-3.csv" does not match pattern: "/demo-[12].csv$/i".
            ##teamcity[testFinished name='filename_pattern at column' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo-3.csv' flowId='42']
            
            
            Found 9 issues in 3 out of 3 CSV files.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testMultipleCsvOptions(): void
    {
        [$expected, $expectedCode] = Tools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
        ]);

        $actual = Tools::realExecution(
            'validate:csv ' . \implode(' ', [
                '--csv="./tests/fixtures/batch/sub/demo-3.csv"',
                '--csv="./tests/fixtures/batch/demo-1.csv"',
                '--csv="./tests/fixtures/batch/demo-2.csv"',
                '--csv="./tests/fixtures/batch/*.csv"',
                '--schema="./tests/schemas/demo_invalid.yml"',
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
}
