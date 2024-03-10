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

final class SchemaTest extends PHPUnit
{
    private const SCHEMA_EXAMPLE_EMPTY = __DIR__ . '/schemas/example_empty.yml';
    private const SCHEMA_EXAMPLE_FULL  = __DIR__ . '/schemas/example_full.yml';

    public function testFilename(): void
    {
        $schemaEmpty = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isSame(self::SCHEMA_EXAMPLE_EMPTY, $schemaEmpty->getFilename());

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isSame(self::SCHEMA_EXAMPLE_FULL, $schemaFull->getFilename());
    }

    public function testGetFinenamePattern(): void
    {
        $schemaEmpty = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isSame(null, $schemaEmpty->getFinenamePattern());

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isSame('^example\.csv$', $schemaFull->getFinenamePattern());
    }

    public function testScvStruture(): void
    {
        $schemaEmpty = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isSame([
            'inherit'                => null,
            'bom'                    => false,
            'delimiter'              => ',',
            'quote_char'             => '\\',
            'enclosure'              => '"',
            'encoding'               => 'utf-8',
            'header'                 => true,
            'strict_column_order'    => false,
            'other_columns_possible' => false,
        ], $schemaEmpty->getCsvStructure()->getArrayCopy());

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isSame([
            'inherit'                => 'alias_1',
            'bom'                    => false,
            'delimiter'              => ',',
            'quote_char'             => '\\',
            'enclosure'              => '"',
            'encoding'               => 'utf-8',
            'header'                 => true,
            'strict_column_order'    => true,
            'other_columns_possible' => true,
        ], $schemaFull->getCsvStructure()->getArrayCopy());
    }

    public function testColumns(): void
    {
        $schemaEmpty = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isSame([], $schemaEmpty->getColumns());

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isSame([
            0  => 0,
            1  => 'General available options',
            2  => 'Some String',
            3  => 'Some Integer',
            4  => 'Some Float',
            5  => 'Some Date',
            6  => 'Some Enum',
            7  => 'Some Boolean',
            8  => 'Some Inherited',
            9  => 'Some Latitude',
            10 => 'Some Longitude',
            11 => 'Some URL',
            12 => 'Some Email',
            13 => 'Some IP',
            14 => 'Some UUID',
            15 => 'Some Custom Rule',
        ], \array_keys($schemaFull->getColumns()));
    }

    public function testColumnByNameAndId(): void
    {
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isNotNull($schemaFull->getColumn(1));
        isNotNull($schemaFull->getColumn('General available options'));

        isSame(
            $schemaFull->getColumn(1),
            $schemaFull->getColumn('General available options'),
        );
    }

    public function testIncludes(): void
    {
        $schemaEmpty = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isSame([], $schemaEmpty->getIncludes());

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isSame([
            'alias_1' => '/path/schema_1.yml',
            'alias_2' => './path/schema_2.yml',
            'alias_3' => '../path/schema_3.yml',
        ], $schemaFull->getIncludes());
    }

    public function testGetUndefinedColumnById(): void
    {
        $this->expectExceptionMessage(
            'Column "1000" not found in schema "' . self::SCHEMA_EXAMPLE_EMPTY . '"',
        );
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isNull($schemaFull->getColumn(1000));
    }

    public function testGetUndefinedColumnByName(): void
    {
        $this->expectExceptionMessage(
            'Column "undefined_column" not found in schema "' . self::SCHEMA_EXAMPLE_EMPTY . '"',
        );
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isNull($schemaFull->getColumn('undefined_column'));
    }

    public function testGetColumnMinimal(): void
    {
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        $column     = $schemaFull->getColumn(0);

        isSame('', $column->getName());
        isSame('', $column->getDescription());
        isSame('base', $column->getType());
        isSame(null, $column->getRegex());
        isSame('', $column->getInherit());
        isFalse($column->isRequired());

        isTrue(\is_array($column->getRules()));
        isEmpty($column->getRules());

        isTrue(\is_array($column->getAggregateRules()));
        isEmpty($column->getAggregateRules());
    }

    public function testGetColumnProps(): void
    {
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        $column     = $schemaFull->getColumn(1);

        isSame('General available options', $column->getName());
        isSame('Some description', $column->getDescription());
        isSame('some_type', $column->getType());
        isSame('', $column->getInherit());

        isTrue($column->isRequired());

        isTrue(\is_array($column->getRules()));
        isNotEmpty($column->getRules());

        isTrue(\is_array($column->getAggregateRules()));
        isNotEmpty($column->getAggregateRules());
    }

    public function testGetColumnRules(): void
    {
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        $column     = $schemaFull->getColumn('Some String');

        isSame([
            'min_length'      => 1,
            'max_length'      => 10,
            'only_trimed'     => false,
            'only_uppercase'  => false,
            'only_lowercase'  => false,
            'only_capitalize' => false,
        ], $column->getRules());
    }

    public function testGetColumnAggregateRules(): void
    {
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        $column     = $schemaFull->getColumn(1);

        isSame([
            'unique'           => false,
            'sorted'           => 'asc',
            'sorted_flag'      => 'SORT_NATURAL',
            'count_min'        => 1,
            'count_max'        => 10,
            'count_empty_min'  => 1,
            'count_empty_max'  => 10,
            'count_filled_min' => 1,
            'count_filled_max' => 10,
            'custom_1'         => [
                'class' => 'My\\Aggregate\\Rules1',
                'args'  => ['value'],
            ],
            'custom_2' => [
                'class' => 'My\\Aggregate\\Rules2',
                'args'  => ['value1', 'value2'],
            ],
            'custom_my_favorite_name' => [
                'class' => 'My\\Aggregate\\RulesXXX',
                'args'  => [],
            ],
        ], $column->getAggregateRules());
    }
}
