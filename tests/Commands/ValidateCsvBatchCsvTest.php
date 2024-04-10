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
        $optionsAsString = Tools::arrayToOptions([
            'csv' => [
                './tests/fixtures/batch/*.csv',
                './tests/fixtures/demo.csv',
            ],
            'schema' => Tools::DEMO_YML_VALID,
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1 (Apply All)
            Found CSV files : 4
            Pairs by pattern: 4
            
            Check schema syntax: 1
              OK ./tests/schemas/demo_valid.yml
            
            CSV file validation: 4
            Schema: ./tests/schemas/demo_valid.yml
              (1/4) OK ./tests/fixtures/batch/demo-1.csv; Size: 123.34 MB
              (2/4) OK ./tests/fixtures/batch/demo-2.csv; Size: 123.34 MB
              (3/4) OK ./tests/fixtures/batch/sub/demo-3.csv; Size: 123.34 MB
              (4/4) OK ./tests/fixtures/demo.csv; Size: 123.34 MB
            
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
            'report' => 'text',
        ];
        $optionsAsString = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1 (Apply All)
            Found CSV files : 3
            Pairs by pattern: 3
            
            Check schema syntax: 1
              2 issues in ./tests/schemas/demo_invalid.yml
                "is_float", column "2:Float". Value "Qwerty" is not a float number.
                "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
                
            
            CSV file validation: 3
            Schema: ./tests/schemas/demo_invalid.yml
              (1/3) 5 issues in ./tests/fixtures/batch/demo-1.csv; Size: 123.34 MB
                "allow_extra_columns" at line 1. Column(s) not found in CSV: "wrong_column_name".
                "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
                "ag:nth_num" at line 1, column "2:Float". The column does not have a line 4, so the value cannot be checked.
                "ag:nth" at line 1, column "3:Birthday". The value on line 2 in the column is "1998-02-28", which is not equal than the expected "2000-12-01".
                "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
                
              (2/3) 7 issues in ./tests/fixtures/batch/demo-2.csv; Size: 123.34 MB
                "allow_extra_columns" at line 1. Column(s) not found in CSV: "wrong_column_name".
                "length_min" at line 2, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
                "length_min" at line 7, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
                "date_min" at line 2, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
                "date_min" at line 4, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
                "date_max" at line 5, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
                "ag:nth" at line 1, column "3:Birthday". The value on line 2 in the column is "1989-05-15", which is not equal than the expected "2000-12-01".
                
              (3/3) 1 issue in ./tests/fixtures/batch/sub/demo-3.csv; Size: 123.34 MB
                "allow_extra_columns" at line 1. Column(s) not found in CSV: "wrong_column_name".
                
            
            Summary:
              3 pairs (schema to csv) were found based on `filename_pattern`.
              Found 2 issues in 1 schemas.
              Found 13 issues in 3 out of 3 CSV files.
            
            
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
                '--no-ansi',
            ]),
            [],
            '',
        );

        // Remove version
        $actual = \preg_replace('/^.+\n/', '', $actual);
        $expected = \preg_replace('/^.+\n/', '', $expected);

        // Remove file size
        $actual = \preg_replace('/; Size: .*B/', '; Size: 123.34 MB', $actual);

        isNotEmpty($expected);
        isNotEmpty($actual);
        isSame($expectedCode, 1);
        isSame($expected, $actual);
    }
}
