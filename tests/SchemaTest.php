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

final class SchemaTest extends TestCase
{
    public function testFilename(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame(Tools::SCHEMA_EXAMPLE_EMPTY, $schemaEmpty->getFilename());

        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        isSame(Tools::SCHEMA_FULL, $schemaFull->getFilename());
    }

    public function testGetFinenamePattern(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame(null, $schemaEmpty->getFilenamePattern());

        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        isSame('/demo(-\d+)?\.csv$/i', $schemaFull->getFilenamePattern());
    }

    public function testScvStruture(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame([
            // 'inherit'                => null,
            'header'     => true,
            'delimiter'  => ',',
            'quote_char' => '\\',
            'enclosure'  => '"',
            'encoding'   => 'utf-8',
            'bom'        => false,
            // 'strict_column_order'    => false,
            // 'other_columns_possible' => false,
        ], $schemaEmpty->getCsvStructure()->getArrayCopy());

        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        isSame([
            // 'inherit'                => 'alias_1',
            'header'     => true,
            'delimiter'  => ',',
            'quote_char' => '\\',
            'enclosure'  => '"',
            'encoding'   => 'utf-8',
            'bom'        => false,
            // 'strict_column_order'    => true,
            // 'other_columns_possible' => true,
        ], $schemaFull->getCsvStructure()->getArrayCopy());
    }

    public function testColumns(): void
    {
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame([], $schemaEmpty->getColumns());

        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        isSame([
            0 => 'Column Name (header)',
            1 => 'another_column',
            2 => 'third_column',
            3 => 3,
        ], \array_keys($schemaFull->getColumns()));
    }

    public function testColumnByNameAndId(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        isNotNull($schemaFull->getColumn(0));
        isNotNull($schemaFull->getColumn('Column Name (header)'));

        isSame(
            $schemaFull->getColumn(0),
            $schemaFull->getColumn('Column Name (header)'),
        );
    }

    public function testIncludes(): void
    {
        skip('Implement me!');
        $schemaEmpty = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isSame([], $schemaEmpty->getIncludes());

        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        isSame([
            'alias_1' => '/path/schema_1.yml',
            'alias_2' => './path/schema_2.yml',
            'alias_3' => '../path/schema_3.yml',
        ], $schemaFull->getIncludes());
    }

    public function testGetUndefinedColumnById(): void
    {
        $this->expectExceptionMessage(
            'Column "1000" not found in schema "' . Tools::SCHEMA_EXAMPLE_EMPTY . '"',
        );
        $schemaFull = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isNull($schemaFull->getColumn(1000));
    }

    public function testGetUndefinedColumnByName(): void
    {
        $this->expectExceptionMessage(
            'Column "undefined_column" not found in schema "' . Tools::SCHEMA_EXAMPLE_EMPTY . '"',
        );
        $schemaFull = new Schema(Tools::SCHEMA_EXAMPLE_EMPTY);
        isNull($schemaFull->getColumn('undefined_column'));
    }

    public function testGetColumnMinimal(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        $column     = $schemaFull->getColumn(0);

        isSame('Column Name (header)', $column->getName());
        isSame('Lorem ipsum', $column->getDescription());
        isSame('', $column->getInherit());
        isFalse($column->isRequired());

        isTrue(\is_array($column->getRules()));
        isNotEmpty($column->getRules());

        isTrue(\is_array($column->getAggregateRules()));
        isNotEmpty($column->getAggregateRules());
    }

    public function testGetColumnProps(): void
    {
        $schemaFull = new Schema(Tools::SCHEMA_FULL);
        $column     = $schemaFull->getColumn(0);

        isSame('Column Name (header)', $column->getName());
        isSame('Lorem ipsum', $column->getDescription());

        isFalse($column->isRequired());

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
            'num_max'   => 74606,
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
}
