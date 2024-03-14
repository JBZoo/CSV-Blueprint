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

namespace JBZoo\PHPUnit\Blueprint;

use JBZoo\PHPUnit\PHPUnit;
use JBZoo\PHPUnit\TestTools;
use JBZoo\Utils\Cli;
use Symfony\Component\Console\Input\StringInput;

use function JBZoo\PHPUnit\isNotEmpty;
use function JBZoo\PHPUnit\isSame;

final class ValidateCsvTest extends PHPUnit
{
    public function testValidateOneFilePositive(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
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

        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => "{$rootPath}/tests/fixtures/demo.csv", // Full path
            'schema' => './tests/schemas/demo_invalid.yml',    // Relative path
        ]);

        TestTools::dumpText($actual);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 1
            
            (1/1) Invalid file: ./tests/fixtures/demo.csv
            +------+------------------+------------------+------------- demo.csv -----------------------------------------------------------+
            | Line | id:Column        | Rule             | Message                                                                          |
            +------+------------------+------------------+----------------------------------------------------------------------------------+
            | 1    |                  | filename_pattern | Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i" |
            | 6    | 0:Name           | min_length       | Value "Carl" (length: 4) is too short. Min length is 5                           |
            | 11   | 0:Name           | min_length       | Value "Lois" (length: 4) is too short. Min length is 5                           |
            | 1    | 1:City           | ag:unique        | Column has non-unique values. Total: 10, unique: 9                               |
            | 5    | 2:Float          | max              | Value "74605.944" is greater than "74605"                                        |
            | 6    | 3:Birthday       | min_date         | Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00" |
            | 8    | 3:Birthday       | min_date         | Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00" |
            | 9    | 3:Birthday       | max_date         | Value "2010-07-20" is more than the maximum date "2009-01-01T00:00:00.000+00:00" |
            | 5    | 4:Favorite color | allow_values     | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]            |
            +------+------------------+------------------+------------- demo.csv -----------------------------------------------------------+
            
            
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
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', $options);

        TestTools::dumpText($actual);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            +------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+
            | Line | id:Column        | Rule         | Message                                                               |
            +------+------------------+--------------+-----------------------------------------------------------------------+
            | 1    | 1:City           | ag:unique    | Column has non-unique values. Total: 2, unique: 1                     |
            | 3    | 2:Float          | max          | Value "74605.944" is greater than "74605"                             |
            | 3    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            +------+------------+------------+------------------ demo-2.csv ----------------------------------------------------+
            | Line | id:Column  | Rule       | Message                                                                          |
            +------+------------+------------+----------------------------------------------------------------------------------+
            | 2    | 0:Name     | min_length | Value "Carl" (length: 4) is too short. Min length is 5                           |
            | 7    | 0:Name     | min_length | Value "Lois" (length: 4) is too short. Min length is 5                           |
            | 2    | 3:Birthday | min_date   | Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00" |
            | 4    | 3:Birthday | min_date   | Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00" |
            | 5    | 3:Birthday | max_date   | Value "2010-07-20" is more than the maximum date "2009-01-01T00:00:00.000+00:00" |
            +------+------------+------------+------------------ demo-2.csv ----------------------------------------------------+
            
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
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/**/demo.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
        ]);

        TestTools::dumpText($actual);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 1
            
            (1/1) Invalid file: ./tests/fixtures/demo.csv
            "filename_pattern" at line 1, column "". Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i".
            "min_length" at line 6, column "0:Name". Value "Carl" (length: 4) is too short. Min length is 5.
            "min_length" at line 11, column "0:Name". Value "Lois" (length: 4) is too short. Min length is 5.
            "ag:unique" at line 1, column "1:City". Column has non-unique values. Total: 10, unique: 9.
            "max" at line 5, column "2:Float". Value "74605.944" is greater than "74605".
            "min_date" at line 6, column "3:Birthday". Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00".
            "min_date" at line 8, column "3:Birthday". Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00".
            "max_date" at line 9, column "3:Birthday". Value "2010-07-20" is more than the maximum date "2009-01-01T00:00:00.000+00:00".
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
            "ag:unique" at line 1, column "1:City". Column has non-unique values. Total: 2, unique: 1.
            
            (2/3) Skipped: ./tests/fixtures/batch/demo-2.csv
            (3/3) Skipped: ./tests/fixtures/batch/sub/demo-3.csv
            
            Found 1 issues in 1 out of 3 CSV files.
            
            TXT;

        // No option (default behavior)
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'Q'      => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Shortcut
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'Q'      => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Shortcut 2
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'quick'  => null,
        ]);
        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Value - yes
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'quick'  => 'yes',
        ]);

        isSame(1, $exitCode, $actual);
        isSame($expectedQuick, $actual);

        // Value - no
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
            'quick'  => 'no',
        ]);

        $expectedFull = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            "ag:unique" at line 1, column "1:City". Column has non-unique values. Total: 2, unique: 1.
            "max" at line 3, column "2:Float". Value "74605.944" is greater than "74605".
            "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            "min_length" at line 2, column "0:Name". Value "Carl" (length: 4) is too short. Min length is 5.
            "min_length" at line 7, column "0:Name". Value "Lois" (length: 4) is too short. Min length is 5.
            "min_date" at line 2, column "3:Birthday". Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00".
            "min_date" at line 4, column "3:Birthday". Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00".
            "max_date" at line 5, column "3:Birthday". Value "2010-07-20" is more than the maximum date "2009-01-01T00:00:00.000+00:00".
            
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

        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'teamcity',
        ]);

        TestTools::dumpText($actual);

        $expected = <<<'TXT'
            Schema: ./tests/schemas/demo_invalid.yml
            Found CSV files: 3
            
            (1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
            
            ##teamcity[testCount count='3' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-1.csv' flowId='42']
            
            ##teamcity[testStarted name='ag:unique at column 1:City' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "ag:unique" at line 1, column "1:City". Column has non-unique values. Total: 2, unique: 1.
            ##teamcity[testFinished name='ag:unique at column 1:City' flowId='42']
            
            ##teamcity[testStarted name='max at column 2:Float' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "max" at line 3, column "2:Float". Value "74605.944" is greater than "74605".
            ##teamcity[testFinished name='max at column 2:Float' flowId='42']
            
            ##teamcity[testStarted name='allow_values at column 4:Favorite color' locationHint='php_qn://./tests/fixtures/batch/demo-1.csv' flowId='42']
            "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            ##teamcity[testFinished name='allow_values at column 4:Favorite color' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo-1.csv' flowId='42']
            
            (2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
            
            ##teamcity[testCount count='5' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo-2.csv' flowId='42']
            
            ##teamcity[testStarted name='min_length at column 0:Name' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "min_length" at line 2, column "0:Name". Value "Carl" (length: 4) is too short. Min length is 5.
            ##teamcity[testFinished name='min_length at column 0:Name' flowId='42']
            
            ##teamcity[testStarted name='min_length at column 0:Name' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "min_length" at line 7, column "0:Name". Value "Lois" (length: 4) is too short. Min length is 5.
            ##teamcity[testFinished name='min_length at column 0:Name' flowId='42']
            
            ##teamcity[testStarted name='min_date at column 3:Birthday' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "min_date" at line 2, column "3:Birthday". Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00".
            ##teamcity[testFinished name='min_date at column 3:Birthday' flowId='42']
            
            ##teamcity[testStarted name='min_date at column 3:Birthday' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "min_date" at line 4, column "3:Birthday". Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00".
            ##teamcity[testFinished name='min_date at column 3:Birthday' flowId='42']
            
            ##teamcity[testStarted name='max_date at column 3:Birthday' locationHint='php_qn://./tests/fixtures/batch/demo-2.csv' flowId='42']
            "max_date" at line 5, column "3:Birthday". Value "2010-07-20" is more than the maximum date "2009-01-01T00:00:00.000+00:00".
            ##teamcity[testFinished name='max_date at column 3:Birthday' flowId='42']
            
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
        [$expected, $expectedCode] = TestTools::virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
        ]);

        $actual = TestTools::realExecution(
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
