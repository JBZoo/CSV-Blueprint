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

final class CreateSchemaTest extends TestCase
{
    public function testWithoutHeader(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create:schema', [
            'csv' => './tests/fixtures/demo.csv',
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/demo.csv"
            name: 'Schema for demo.csv'
            description: |-
              CSV file ./tests/fixtures/demo.csv
              Suggested schema based on the first 10000 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /demo\.csv$/
            csv:
              header: false
            columns:
              - example: Name
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 7
                  is_trimmed: true
                  is_capitalize: true
                  word_count: 1
                aggregate_rules:
                  is_unique: true
            
              - example: City
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  word_count: 1
            
              - example: Float
                rules:
                  not_empty: true
                  length_min: 1
                  length_max: 8
                  is_trimmed: true
                  is_capitalize: true
                  num_min: -200.1
                  num_max: 4825.185
                  precision_min: 0
                  precision_max: 4
                aggregate_rules:
                  is_unique: true
            
              - example: Birthday
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 10
                  is_trimmed: true
                  is_capitalize: true
                  word_count_min: 1
                  word_count_max: 2
            
              - example: 'Favorite color'
                rules:
                  not_empty: true
                  allow_values:
                    - 'Favorite color'
                    - green
                    - blue
                    - red
            
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testWithHeader(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create:schema', [
            'csv'    => './tests/fixtures/demo.csv',
            'header' => 'true',
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/demo.csv"
            name: 'Schema for demo.csv'
            description: |-
              CSV file ./tests/fixtures/demo.csv
              Suggested schema based on the first 10000 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /demo\.csv$/
            columns:
              - name: Name
                example: Clyde
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 7
                  is_trimmed: true
                  is_capitalize: true
                  word_count: 1
                aggregate_rules:
                  is_unique: true
            
              - name: City
                example: Rivsikgo
                rules:
                  not_empty: true
                  length_min: 6
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  word_count: 1
            
              - name: Float
                example: '4825.185'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -200.1
                  num_max: 4825.185
                  precision_min: 0
                  precision_max: 4
                aggregate_rules:
                  is_unique: true
            
              - name: Birthday
                example: '2000-01-01'
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '1988-08-24'
                  date_max: '2010-07-20'
            
              - name: 'Favorite color'
                example: green
                rules:
                  not_empty: true
                  allow_values:
                    - green
                    - blue
                    - red
            
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }
}
