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

final class DebugSchemaTest extends TestCase
{
    public function testDumpWithDefaults(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('debug-schema', [
            'schema' => Tools::DEMO_YML_VALID,
        ]);

        $expected = <<<'YAML'
            # Schema file is "./tests/schemas/demo_valid.yml"
            name: ''
            description: ''
            presets: []
            filename_pattern: '/(demo|demo_.*|demo-.*)\.csv$/'
            csv:
              header: true
              delimiter: ','
              quote_char: \
              enclosure: '"'
              encoding: utf-8
              bom: false
            structural_rules:
              strict_column_order: true
              allow_extra_columns: false
            columns:
              - name: Name
                description: ''
                example: ~
                required: true
                extra: 123
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 7
                aggregate_rules:
                  is_unique: true
            
              - name: City
                description: ''
                example: ~
                required: true
                extra: ~
                rules:
                  not_empty: true
                  is_capitalize: true
                aggregate_rules: []
            
              - name: Float
                description: ''
                example: ~
                required: true
                extra:
                  custom_key: custom_value
                  custom_key_2: custom_value_2
                rules:
                  not_empty: true
                  is_float: true
                  num_min: -19366059128
                  num_max: 4825.186
                aggregate_rules:
                  sum_max: 4692
            
              - name: Birthday
                description: ''
                example: ~
                required: true
                extra:
                  - 123
                  - 456
                rules:
                  not_empty: true
                  date_format: Y-m-d
                aggregate_rules: []
            
              - name: 'Favorite color'
                description: ''
                example: ~
                required: true
                extra: ~
                rules:
                  not_empty: true
                  allow_values:
                    - red
                    - green
                    - blue
                aggregate_rules: []
            extra: 123
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testDumpHideDefaults(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('debug-schema', [
            'schema'        => Tools::DEMO_YML_VALID,
            'hide-defaults' => null,
        ]);

        $expected = <<<'YAML'
            # Schema file is "./tests/schemas/demo_valid.yml"
            filename_pattern: '/(demo|demo_.*|demo-.*)\.csv$/'
            columns:
              - name: Name
                extra: 123
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 7
                aggregate_rules:
                  is_unique: true
            
              - name: City
                rules:
                  not_empty: true
                  is_capitalize: true
            
              - name: Float
                extra:
                  custom_key: custom_value
                  custom_key_2: custom_value_2
                rules:
                  not_empty: true
                  is_float: true
                  num_min: -19366059128
                  num_max: 4825.186
                aggregate_rules:
                  sum_max: 4692
            
              - name: Birthday
                extra:
                  - 123
                  - 456
                rules:
                  not_empty: true
                  date_format: Y-m-d
            
              - name: 'Favorite color'
                rules:
                  not_empty: true
                  allow_values:
                    - red
                    - green
                    - blue
            extra: 123
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testDumpPresetUsage(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('debug-schema', [
            'schema'        => './schema-examples/preset_usage.yml',
            'hide-defaults' => null,
        ]);

        $expected = <<<'YAML'
            # Schema file is "./schema-examples/preset_usage.yml"
            name: 'Schema uses presets and add new columns + specific rules.'
            description: 'This schema uses presets. Also, it demonstrates how to override preset values.'
            presets:
              users: <root>/schema-examples/preset_users.yml
              db: <root>/schema-examples/preset_database.yml
            csv:
              delimiter: ;
              enclosure: '|'
            columns:
              - name: id
                description: 'Unique identifier, usually used to denote a primary key in databases.'
                example: 12345
                extra:
                  custom_key: 'custom value'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_int: true
                  num_min: 1
                aggregate_rules:
                  is_unique: true
                  sorted:
                    - asc
                    - numeric
            
              - name: status
                description: 'Status in database'
                example: active
                rules:
                  not_empty: true
                  allow_values:
                    - active
                    - inactive
                    - pending
                    - deleted
            
              - name: login
                description: "User's login name"
                example: johndoe
                rules:
                  not_empty: true
                  length_min: 3
                  length_max: 20
                  is_trimmed: true
                  is_lowercase: true
                  is_slug: true
                  is_alnum: true
                aggregate_rules:
                  is_unique: true
            
              - name: email
                description: "User's email address"
                example: user@example.com
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_lowercase: true
                  is_email: true
                aggregate_rules:
                  is_unique: true
            
              - name: full_name
                description: "User's full name"
                example: 'John Doe Smith'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_capitalize: true
                  word_count_min: 2
                  word_count_max: 8
                  contains: ' '
                  charset: UTF-8
                aggregate_rules:
                  is_unique: true
            
              - name: birthday
                description: "Validates the user's birthday."
                example: '1990-01-01'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_date: true
                  date_max: now
                  date_format: Y-m-d
                  date_age_greater: 0
                  date_age_less: 150
            
              - name: phone
                description: "User's phone number in US"
                example: '+1 650 253 00 00'
                rules:
                  not_empty: true
                  is_trimmed: true
                  starts_with: '+1'
                  phone: US
            
              - name: password
                description: "User's password"
                example: 9RfzE$8NKD
                rules:
                  not_empty: true
                  length_min: 10
                  length_max: 20
                  is_trimmed: true
                  contains_none:
                    - password
                    - '123456'
                    - qwerty
                    - ' '
                  password_strength_min: 7
                  is_password_safe_chars: true
                  charset: UTF-8
            
              - name: admin_note
                description: 'Admin note'
                rules:
                  not_empty: true
                  length_min: 1
                  length_max: 10
                aggregate_rules:
                  is_unique: true
                  sorted:
                    - asc
                    - numeric
            
            YAML;

        $actual = \str_replace(PROJECT_ROOT, '<root>', $actual);

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }
}
