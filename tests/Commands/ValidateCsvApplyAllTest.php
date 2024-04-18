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

final class ValidateCsvApplyAllTest extends TestCase
{
    public function testNoPatternApplyAllAutoNegativeMany(): void
    {
        $optionsAsString = Tools::arrayToOptions([
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => [
                Tools::DEMO_YML_VALID,
                './tests/schemas/demo_invalid_no_pattern.yml',
            ],
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 2
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 2
              (1/2) OK ./tests/schemas/demo_invalid_no_pattern.yml
              (2/2) OK ./tests/schemas/demo_valid.yml
            
            CSV file validation: 1
            Schema: ./tests/schemas/demo_valid.yml
              OK ./tests/fixtures/demo.csv; Size: 123.34 MB
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 2 schemas.
              No issues in 1 CSV files.
              Not used schemas:
                * ./tests/schemas/demo_invalid_no_pattern.yml
              Looks good!
            
            
            TXT;

        isSame(0, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testNoPatternApplyAllAutoNegativeGlob(): void
    {
        $optionsAsString = Tools::arrayToOptions([
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => './tests/schemas/demo_invalid_*.yml',
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 0
            
            Check schema syntax: 1
              OK ./tests/schemas/demo_invalid_no_pattern.yml
            
            CSV file validation: 0
            
            Summary:
              0 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              No issues in 1 CSV files.
              No schema was applied to the CSV files (filename_pattern didn't match):
                * ./tests/fixtures/demo.csv
              Not used schemas:
                * ./tests/schemas/demo_invalid_no_pattern.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testNoPatternApplyAllAutoPositive(): void
    {
        $optionsAsString = Tools::arrayToOptions([
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => './tests/schemas/demo_invalid_no_pattern.yml',
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1 (Apply All)
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
              OK ./tests/schemas/demo_invalid_no_pattern.yml
            
            CSV file validation: 1
            Schema: ./tests/schemas/demo_invalid_no_pattern.yml
              2 issues in ./tests/fixtures/demo.csv; Size: 123.34 MB
                +------+-----------+---------+---------------------------------------------------+
                | Line | id:Column | Rule    | Message                                           |
                +------+-----------+---------+---------------------------------------------------+
                |    4 | 2:Float   | num_min | The value "-177.90" is less than the expected "0" |
                |   11 | 2:Float   | num_min | The value "-200.1" is less than the expected "0"  |
                +------+-----------+---------+---------------------------------------------------+
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              Found 2 issues in 1 out of 1 CSV files.
              Schemas have no filename_pattern and are applied to all CSV files found:
                * ./tests/schemas/demo_invalid_no_pattern.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testNoPatternApplyAllYes(): void
    {
        $optionsAsString = Tools::arrayToOptions([
            'csv'       => './tests/fixtures/demo.csv',
            'schema'    => [Tools::DEMO_YML_VALID, './tests/schemas/demo_invalid_no_pattern.yml'],
            'apply-all' => 'yes',
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 2 (Apply All)
            Found CSV files : 1
            Pairs by pattern: 2
            
            Check schema syntax: 2
              (1/2) OK ./tests/schemas/demo_invalid_no_pattern.yml
              (2/2) OK ./tests/schemas/demo_valid.yml
            
            CSV file validation: 2
            Schema: ./tests/schemas/demo_invalid_no_pattern.yml
              (1/2) 2 issues in ./tests/fixtures/demo.csv; Size: 123.34 MB
                +------+-----------+---------+---------------------------------------------------+
                | Line | id:Column | Rule    | Message                                           |
                +------+-----------+---------+---------------------------------------------------+
                |    4 | 2:Float   | num_min | The value "-177.90" is less than the expected "0" |
                |   11 | 2:Float   | num_min | The value "-200.1" is less than the expected "0"  |
                +------+-----------+---------+---------------------------------------------------+
            
            Schema: ./tests/schemas/demo_valid.yml
              (2/2) OK ./tests/fixtures/demo.csv; Size: 123.34 MB
            
            Summary:
              2 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 2 schemas.
              Found 2 issues in 1 out of 1 CSV files.
              Schemas have no filename_pattern and are applied to all CSV files found:
                * ./tests/schemas/demo_invalid_no_pattern.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testNoPatternApplyAllYesGlob(): void
    {
        $optionsAsString = Tools::arrayToOptions([
            'csv'       => './tests/fixtures/demo.csv',
            'schema'    => './tests/schemas/demo_invalid_*.yml',
            'apply-all' => 'yes',
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1 (Apply All)
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
              OK ./tests/schemas/demo_invalid_no_pattern.yml
            
            CSV file validation: 1
            Schema: ./tests/schemas/demo_invalid_no_pattern.yml
              2 issues in ./tests/fixtures/demo.csv; Size: 123.34 MB
                +------+-----------+---------+---------------------------------------------------+
                | Line | id:Column | Rule    | Message                                           |
                +------+-----------+---------+---------------------------------------------------+
                |    4 | 2:Float   | num_min | The value "-177.90" is less than the expected "0" |
                |   11 | 2:Float   | num_min | The value "-200.1" is less than the expected "0"  |
                +------+-----------+---------+---------------------------------------------------+
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              Found 2 issues in 1 out of 1 CSV files.
              Schemas have no filename_pattern and are applied to all CSV files found:
                * ./tests/schemas/demo_invalid_no_pattern.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testNoPatternApplyAllNo(): void
    {
        $optionsAsString = Tools::arrayToOptions([
            'csv'       => './tests/fixtures/demo.csv',
            'schema'    => './tests/schemas/demo_invalid_no_pattern.yml',
            'apply-all' => 'no',
        ]);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $optionsAsString);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 0
            
            Check schema syntax: 1
              OK ./tests/schemas/demo_invalid_no_pattern.yml
            
            CSV file validation: 0
            
            Summary:
              0 pairs (schema to csv) were found based on `filename_pattern`.
              No issues in 1 schemas.
              No issues in 1 CSV files.
              No schema was applied to the CSV files (filename_pattern didn't match):
                * ./tests/fixtures/demo.csv
              Not used schemas:
                * ./tests/schemas/demo_invalid_no_pattern.yml
            
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }
}
