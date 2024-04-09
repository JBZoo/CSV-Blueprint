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

final class ValidateSchemaTest extends TestCase
{
    public function testValid(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:schema', [
            'schema' => Tools::DEMO_YML_VALID,
        ]);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found schemas: 1
            
            OK ./tests/schemas/demo_valid.yml
            
            TXT;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testInvalidSyntax(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:schema', [
            'schema' => './tests/schemas/broken/syntax.yml',
        ]);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found schemas: 1
            
            1 issue in ./tests/schemas/broken/syntax.yml
              +------+-----------+---------------+---------------------------------------------------+
              | Line | id:Column | Rule          | Message                                           |
              +------+-----------+---------------+---------------------------------------------------+
              |   15 |           | schema.syntax | Unable to parse at line 15 (near "(*$#)@(@$*)("). |
              +------+-----------+---------------+---------------------------------------------------+
            
            TXT;

        isSame($expected, $actual);
        isSame(1, $exitCode, $actual);
    }

    public function testInvalidSchemas(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:schema', [
            'schema' => './tests/schemas/broken/*.yml',
        ]);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found schemas: 2
            
            (1/2) 1 issue in ./tests/schemas/broken/invalid_schema.yml
              +-------+-----------+--------+----------------------------------+
              |  Line | id:Column | Rule   | Message                          |
              +-------+-----------+--------+----------------------------------+
              | undef | meta      | schema | Unknown key: .unknow_root_option |
              +-------+-----------+--------+----------------------------------+
            (2/2) 1 issue in ./tests/schemas/broken/syntax.yml
              +------+-----------+---------------+---------------------------------------------------+
              | Line | id:Column | Rule          | Message                                           |
              +------+-----------+---------------+---------------------------------------------------+
              |   15 |           | schema.syntax | Unable to parse at line 15 (near "(*$#)@(@$*)("). |
              +------+-----------+---------------+---------------------------------------------------+
            
            TXT;

        isSame($expected, $actual);
        isSame(1, $exitCode, $actual);
    }

    public function testInvalidSchemasTextReport(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:schema', [
            'schema' => './tests/schemas/broken/*.yml',
            'report' => 'text',
        ]);

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found schemas: 2

            (1/2) 1 issue in ./tests/schemas/broken/invalid_schema.yml
              "schema", column "meta". Unknown key: .unknow_root_option.
              
            (2/2) 1 issue in ./tests/schemas/broken/syntax.yml
              "schema.syntax" at line 15. Unable to parse at line 15 (near "(*$#)@(@$*)(").
              
            
            TXT;

        isSame($expected, $actual);
        isSame(1, $exitCode, $actual);
    }

    public function testValidExamples(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('validate:schema', Tools::arrayToOptions([
            'schema' => [
                './schema-examples/*.yml',
                './schema-examples/*.json',
                './schema-examples/*.php',
            ],
        ]));

        $expected = <<<'TXT'
            CSV Blueprint: Unknown version (PhpUnit)
            Found schemas: 9
            
            (1/9) OK ./schema-examples/full.json
            (2/9) OK ./schema-examples/full.php
            (3/9) OK ./schema-examples/full.yml
            (4/9) OK ./schema-examples/full_clean.yml
            (5/9) OK ./schema-examples/preset_database.yml
            (6/9) OK ./schema-examples/preset_features.yml
            (7/9) OK ./schema-examples/preset_usage.yml
            (8/9) OK ./schema-examples/preset_users.yml
            (9/9) OK ./schema-examples/readme_sample.yml
            
            TXT;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }
}
