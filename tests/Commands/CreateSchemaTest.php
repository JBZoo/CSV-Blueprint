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

use function JBZoo\PHPUnit\isContain;
use function JBZoo\PHPUnit\isSame;

final class CreateSchemaTest extends TestCase
{
    public function testWithoutHeader(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
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
                  is_alnum: true
                  is_alpha: true
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
                  is_alnum: true
                  is_alpha: true
            
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

        \file_put_contents(PROJECT_ROOT . '/build/demo.schema.yml', $actual);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => PROJECT_ROOT . '/build/demo.schema.yml',
        ]);
        isContain('Pairs by pattern: 1', $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testWithHeader(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
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
                  is_alnum: true
                  is_alpha: true
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
                  is_alnum: true
                  is_alpha: true
            
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
                  date_min: '1955-05-14'
                  date_max: '2010-07-20'
                  is_slug: true
            
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

        \file_put_contents(PROJECT_ROOT . '/build/demo.schema.yml', $actual);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => PROJECT_ROOT . '/build/demo.schema.yml',
        ]);
        isContain('Pairs by pattern: 1', $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testWithHeaderComplex(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
            'csv'    => './tests/fixtures/complex_header.csv',
            'header' => 'true',
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/complex_header.csv"
            name: 'Schema for complex_header.csv'
            description: |-
              CSV file ./tests/fixtures/complex_header.csv
              Suggested schema based on the first 10000 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /complex_header\.csv$/
            columns:
              - name: seq
                example: '1'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_int: true
                  num_min: 1
                  num_max: 100
                  is_hex: true
                  is_slug: true
                  is_angle: true
                  is_longitude: true
                  is_geohash: true
                  is_alnum: true
                aggregate_rules:
                  is_unique: true
            
              - name: bool
                example: 'true'
                rules:
                  not_empty: true
                  allow_values:
                    - 'true'
                    - 'false'
                    - 'False'
                    - 'True'
            
              - name: yn
                example: 'N'
                rules:
                  not_empty: true
                  allow_values:
                    - 'N'
                    - 'Y'
            
              - name: integer
                example: '577928'
                rules:
                  not_empty: false
                  is_trimmed: true
                  is_int: true
                  num_min: -970498
                  num_max: 970879
                aggregate_rules:
                  is_unique: true
            
              - name: float
                example: '-308500353777.664'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -896172733707.06
                  num_max: 863717712252.11
                  precision_min: 0
                  precision_max: 4
                aggregate_rules:
                  is_unique: true
            
              - name: name/first
                example: Emma
                rules:
                  not_empty: true
                  length_min: 3
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  word_count: 1
                  is_alnum: true
                  is_alpha: true
            
              - name: date
                example: 2042/11/18
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '2024-03-04'
                  date_max: '2124-05-22'
                aggregate_rules:
                  is_unique: true
            
              - name: gender
                example: Female
                rules:
                  not_empty: true
                  allow_values:
                    - Female
                    - Male
            
              - name: email
                example: naduka@jamci.kw
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 20
                  is_trimmed: true
                  is_lowercase: true
                  word_count: 3
                  precision_min: 2
                  precision_max: 3
                  is_email: true
                aggregate_rules:
                  is_unique: true
            
              - name: guid
                example: 2feb87a1-a0c2-57f7-82d3-a5eec01cea41
                rules:
                  not_empty: true
                  length: 36
                  is_trimmed: true
                  is_lowercase: true
                  word_count_min: 5
                  word_count_max: 14
                  is_uuid: true
                  is_slug: true
                aggregate_rules:
                  is_unique: true
            
              - name: latitude
                example: '-27.94845'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -89.5128
                  num_max: 89.88597
                  precision_min: 3
                  precision_max: 5
                  is_latitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: longitude
                example: '-143.16108'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -178.20241
                  num_max: 168.90054
                  precision_min: 3
                  precision_max: 5
                  is_longitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: sentence
                example: 'En afu emoharhin itu me rectoge gacoseh tob taug raf tet oh hettulob gom tafba no loka.'
                rules:
                  not_empty: true
                  length_min: 7
                  length_max: 113
                  is_capitalize: true
                  word_count_min: 1
                  word_count_max: 18
                aggregate_rules:
                  is_unique: true
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);

        \file_put_contents(PROJECT_ROOT . '/build/demo.schema.yml', $actual);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', [
            'csv'    => './tests/fixtures/complex_header.csv',
            'schema' => PROJECT_ROOT . '/build/demo.schema.yml',
        ]);
        isContain('Pairs by pattern: 1', $actual);
        isSame(0, $exitCode, $actual);
    }
}
