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

namespace JBZoo\PHPUnit;

use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use Symfony\Component\Finder\Finder;

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;

final class SchemaTest extends TestCase
{
    public function testFilename(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame(Tools::SCHEMA_EXAMPLE_EMPTY, $schemaEmpty->getFilename());

        $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
        isSame(Tools::SCHEMA_FULL_YML, $schemaFull->getFilename());
    }

    public function testGetFinenamePattern(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame(null, $schemaEmpty->getFilenamePattern());

        $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
        isSame('/demo(-\d+)?\.csv$/i', $schemaFull->getFilenamePattern());
    }

    public function testScvStruture(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame([
            'header'     => true,
            'delimiter'  => ',',
            'quote_char' => '\\',
            'enclosure'  => '"',
            'encoding'   => 'utf-8',
            'bom'        => false,
        ], $schemaEmpty->getCsvParserConfig()->getArrayCopy());

        $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
        isSame([
            'header'     => true,
            'delimiter'  => ',',
            'quote_char' => '\\',
            'enclosure'  => '"',
            'encoding'   => 'utf-8',
            'bom'        => false,
        ], $schemaFull->getCsvParserConfig()->getArrayCopy());
    }

    public function testColumns(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame([], $schemaEmpty->getColumns());

        $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
        isSame([
            0 => 'Column Name (header)',
            1 => 'another_column',
            2 => 'third_column',
        ], $schemaFull->getSchemaHeader());
    }

    public function testColumnByNameAndId(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
        isNotNull($schemaFull->getColumn(0));
        isNotNull($schemaFull->getColumn('Column Name (header)'));

        isSame(
            $schemaFull->getColumn(0),
            $schemaFull->getColumn('Column Name (header)'),
        );
    }

    // public function testIncludes(): void
    // {
    //     skip('Implement me!');
    //     $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
    //     isSame([], $schemaEmpty->getIncludes());
    //
    //     $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
    //     isSame([
    //         'alias_1' => '/path/schema_1.yml',
    //         'alias_2' => './path/schema_2.yml',
    //         'alias_3' => '../path/schema_3.yml',
    //     ], $schemaFull->getIncludes());
    // }

    public function testGetUndefinedColumnById(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isNull($schemaFull->getColumn(1000));
    }

    public function testGetUndefinedColumnByName(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isNull($schemaFull->getColumn('undefined_column'));
    }

    public function testGetColumnMinimal(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
        $column = $schemaFull->getColumn(0);

        isSame('Column Name (header)', $column->getName());
        isSame('Lorem ipsum', $column->getDescription());
        isTrue($column->isRequired());

        isTrue(\is_array($column->getRules()));
        isNotEmpty($column->getRules());

        isTrue(\is_array($column->getAggregateRules()));
        isNotEmpty($column->getAggregateRules());
    }

    public function testGetColumnProps(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_FULL_YML);
        $column = $schemaFull->getColumn(0);

        isSame('Column Name (header)', $column->getName());
        isSame('Lorem ipsum', $column->getDescription());

        isTrue($column->isRequired());

        isTrue(\is_array($column->getRules()));
        isNotEmpty($column->getRules());

        isTrue(\is_array($column->getAggregateRules()));
        isNotEmpty($column->getAggregateRules());
    }

    public function testGetColumnRules(): void
    {
        $schema = new Schema(Tools::DEMO_YML_VALID);
        isSame($schema->getColumn('Name'), $schema->getColumn(0));

        isSame([
            'not_empty'  => true,
            'length_min' => 4,
            'length_max' => 7,
        ], $schema->getColumn(0)->getRules());

        isSame([
            'not_empty'     => true,
            'is_capitalize' => true,
        ], $schema->getColumn(1)->getRules());

        isSame([
            'not_empty' => true,
            'is_float'  => true,
            'num_min'   => -19366059128,
            'num_max'   => 4825.186,
        ], $schema->getColumn(2)->getRules());
    }

    public function testGetColumnAggregateRules(): void
    {
        $schema = new Schema(Tools::DEMO_YML_VALID);

        isSame([
            'is_unique' => true,
        ], $schema->getColumn(0)->getAggregateRules());

        isSame([], $schema->getColumn(1)->getAggregateRules());
    }

    public function testValidateItself(): void
    {
        $list = [
            Tools::SCHEMA_FULL_YML  => yml(Tools::SCHEMA_FULL_YML),
            Tools::SCHEMA_FULL_JSON => json(Tools::SCHEMA_FULL_JSON),
            Tools::SCHEMA_FULL_PHP  => phpArray(Tools::SCHEMA_FULL_PHP),
        ];

        $expected = phpArray(Tools::SCHEMA_FULL_PHP)->getArrayCopy();

        foreach ($list as $path => $actual) {
            isSame([], Utils::compareArray($expected, $actual->getArrayCopy()));

            $schema = new Schema($path);
            isSame('', (string)$schema->validate());
        }
    }

    public function testValidateValidSchemaFixtures(): void
    {
        $schemas = (new Finder())
            ->in(PROJECT_ROOT . '/tests/schemas')
            ->in(PROJECT_ROOT . '/tests/Benchmarks')
            ->in(PROJECT_ROOT . '/schema-examples')
            ->name('*.yml')
            ->notName([
                'todo.yml',
                'invalid_schema.yml',
                'demo_invalid.yml',
            ])
            ->files();

        foreach ($schemas as $schemaFile) {
            $filepath = $schemaFile->getPathname();
            isSame('', (string)(new Schema($filepath))->validate(), $filepath);
        }
    }

    public function testValidateInvalidSchema(): void
    {
        $schema = new Schema(Tools::SCHEMA_INVALID);
        isSame(
            <<<'TABLE'
                +-------+------------+--------+----------- invalid_schema.yml ------------------------------------------+
                | Line  | id:Column  | Rule   | Message                                                                 |
                +-------+------------+--------+-------------------------------------------------------------------------+
                | undef | meta       | schema | Unknown key: .unknow_root_option                                        |
                | undef | meta       | schema | Unknown key: .csv.unknow_csv_param                                      |
                | undef | 0:Name     | schema | Unknown key: .columns.0.rules.unknow_rule                               |
                | undef | 1:City     | schema | Unknown key: .columns.1.unknow_colum_option                             |
                | undef | 3:Birthday | schema | Expected type "string", actual "boolean" in .columns.3.rules.date_max   |
                | undef | 4:         | schema | The key "name" must be non-empty because the option "csv.header" = true |
                | undef | 4:         | schema | Expected type "boolean", actual "string" in .columns.4.rules.not_empty  |
                | undef | 4:         | schema | Expected type "array", actual "string" in .columns.4.rules.allow_values |
                +-------+------------+--------+----------- invalid_schema.yml ------------------------------------------+
                
                TABLE,
            $schema->validate()->render(ErrorSuite::RENDER_TABLE),
        );

        isSame(
            <<<'TEXT'
                "schema", column "meta". Unknown key: .unknow_root_option.
                "schema", column "meta". Unknown key: .csv.unknow_csv_param.
                "schema", column "0:Name". Unknown key: .columns.0.rules.unknow_rule.
                "schema", column "1:City". Unknown key: .columns.1.unknow_colum_option.
                "schema", column "3:Birthday". Expected type "<c>string</c>", actual "<green>boolean</green>" in .columns.3.rules.date_max.
                "schema", column "4:". The key "<c>name</c>" must be non-empty because the option "<green>csv.header</green>" = true.
                "schema", column "4:". Expected type "<c>boolean</c>", actual "<green>string</green>" in .columns.4.rules.not_empty.
                "schema", column "4:". Expected type "<c>array</c>", actual "<green>string</green>" in .columns.4.rules.allow_values.
                
                TEXT,
            $schema->validate()->render(ErrorSuite::REPORT_TEXT),
        );
    }

    public function testMatchTypes(): void
    {
        // null|array|bool|float|int|string
        $map = [
            'null'   => null,
            'array'  => [],
            'bool'   => true,
            'float'  => 1.0,
            'int'    => 1,
            'string' => '',
        ];

        foreach ($map as $type => $value) {
            isTrue(Utils::matchTypes($value, $value));
        }

        $expectedIssues = [
            'array !== bool',
            'array !== float',
            'array !== int',
            'array !== null',
            'array !== string',
            'bool !== float',
            'bool !== int',
            'bool !== null',
            'bool !== string',
            'float !== null',
            'int !== null',
            'null !== string',
            'float !== int',
            'int !== string',
        ];

        $invalidPairs = [];

        foreach ($map as $k1 => $expected) {
            foreach ($map as $k2 => $actual) {
                $pair = [$k1, $k2];
                \sort($pair);
                $pair = \implode(' !== ', $pair);

                if (\in_array($pair, $expectedIssues, true)) {
                    continue;
                }

                if (!Utils::matchTypes($expected, $actual)) {
                    $invalidPairs[] = $pair;
                }
            }
        }

        isSame([], $invalidPairs);
    }

    public function testTodoList(): void
    {
        isSame(
            [],
            Tools::findKeysToRemove(
                yml(Tools::SCHEMA_FULL_YML)->getArrayCopy(),
                yml(Tools::SCHEMA_TODO)->getArrayCopy(),
            ),
        );
    }
}
