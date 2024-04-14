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

        $expected = <<<'TXT'
            # Based on CSV "./tests/fixtures/demo.csv"
            name: 'Schema for demo.csv'
            description: |-
              CSV file ./tests/fixtures/demo.csv
              Suggested schema based on the first 1000 lines.
              Please review it before using.
            filename_pattern: /demo\.csv/
            csv:
              header: false
            columns:
              - example: Name
            
              - example: City
                aggregate_rules:
                  is_unique: true
            
              - example: Float
            
              - example: Birthday
                aggregate_rules:
                  is_unique: true
            
              - example: 'Favorite color'
                aggregate_rules:
                  is_unique: true
            
            
            TXT;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testWitHeader(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create:schema', [
            'csv'    => './tests/fixtures/demo.csv',
            'header' => 'true',
        ]);

        $expected = <<<'TXT'
            # Based on CSV "./tests/fixtures/demo.csv"
            name: 'Schema for demo.csv'
            description: |-
              CSV file ./tests/fixtures/demo.csv
              Suggested schema based on the first 1000 lines.
              Please review it before using.
            filename_pattern: /demo\.csv/
            columns:
              - name: Name
                example: Clyde
            
              - name: City
                example: Rivsikgo
                aggregate_rules:
                  is_unique: true
            
              - name: Float
                example: '4825.185'
                rules:
                  is_float: true
            
              - name: Birthday
                example: '2000-01-01'
                aggregate_rules:
                  is_unique: true
            
              - name: 'Favorite color'
                example: green
                aggregate_rules:
                  is_unique: true
            
            
            TXT;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }
}
