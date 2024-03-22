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

final class ValidateCsvBatchCsvTest extends TestCase
{
    public function testValidateManyCsvPositive(): void
    {
        $optionsAsString = Tools::arrayToOptionString([
            'csv' => [
                './tests/fixtures/batch/*.csv',
                './tests/fixtures/demo.csv',
            ],
            'schema' => Tools::DEMO_YML_VALID,
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $optionsAsString);

        $expected = $expected = <<<'TXT'
            Found Schemas   : 1
            Found CSV files : 4
            Pairs by pattern: 4
            
            Check schema syntax: 1
            (1/1) OK: ./tests/schemas/demo_valid.yml
            
            CSV file validation: 4
            (1/4) Schema: ./tests/schemas/demo_valid.yml
            (1/4) CSV   : ./tests/fixtures/batch/demo-1.csv
            (1/4) OK
            (2/4) Schema: ./tests/schemas/demo_valid.yml
            (2/4) CSV   : ./tests/fixtures/batch/demo-2.csv
            (2/4) OK
            (3/4) Schema: ./tests/schemas/demo_valid.yml
            (3/4) CSV   : ./tests/fixtures/batch/sub/demo-3.csv
            (3/4) OK
            (4/4) Schema: ./tests/schemas/demo_valid.yml
            (4/4) CSV   : ./tests/fixtures/demo.csv
            (4/4) OK
            
            Summary:
              4 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              No issues in 4 CSV files.
              Looks good!
            
            
            TXT;

        isSame(0, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testValidateManyCsvNegative(): void
    {
        $options = [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            Found Schemas   : 1
            Found CSV files : 3
            Pairs by pattern: 3
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) Issues: 2
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            | Line  | id:Column        | Rule         | Message                                                              |
            +-------+------------------+--------------+----------------------------------------------------------------------+
            | undef | 2:Float          | is_float     | Value "Qwerty" is not a float number                                 |
            | undef | 4:Favorite color | allow_values | Value "123" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            
            
            CSV file validation: 3
            (1/3) Schema: ./tests/schemas/demo_invalid.yml
            (1/3) CSV   : ./tests/fixtures/batch/demo-1.csv
            (1/3) Issues: 3
            +------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+
            | Line | id:Column        | Rule         | Message                                                               |
            +------+------------------+--------------+-----------------------------------------------------------------------+
            | 1    |                  | csv.header   | Columns not found in CSV: "wrong_column_name"                         |
            | 1    | 1:City           | ag:is_unique | Column has non-unique values. Unique: 1, total: 2                     |
            | 3    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+
            
            (2/3) Schema: ./tests/schemas/demo_invalid.yml
            (2/3) CSV   : ./tests/fixtures/batch/demo-2.csv
            (2/3) Issues: 6
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
            
            (3/3) Schema: ./tests/schemas/demo_invalid.yml
            (3/3) CSV   : ./tests/fixtures/batch/sub/demo-3.csv
            (3/3) Issues: 1
            +------+-----------+------------+- demo-3.csv ----------------------------------+
            | Line | id:Column | Rule       | Message                                       |
            +------+-----------+------------+-----------------------------------------------+
            | 1    |           | csv.header | Columns not found in CSV: "wrong_column_name" |
            +------+-----------+------------+- demo-3.csv ----------------------------------+
            
            
            Summary:
              3 pairs (schema to csv) were found based on `filename_pattern`.
              Found 2 issues in 1 schemas.
              Found 10 issues in 3 out of 3 CSV files.
            
            
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
