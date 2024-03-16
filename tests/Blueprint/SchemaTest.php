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

namespace JBZoo\PHPUnit\Blueprint;

use JBZoo\CsvBlueprint\Schema;
use JBZoo\PHPUnit\PHPUnit;

use function JBZoo\PHPUnit\isFalse;
use function JBZoo\PHPUnit\isNotEmpty;
use function JBZoo\PHPUnit\isNotNull;
use function JBZoo\PHPUnit\isNull;
use function JBZoo\PHPUnit\isSame;
use function JBZoo\PHPUnit\isTrue;
use function JBZoo\PHPUnit\skip;

final class SchemaTest extends PHPUnit
{
    private const SCHEMA_EXAMPLE_EMPTY = PROJECT_TESTS . '/schemas/example_empty.yml';
    private const SCHEMA_EXAMPLE_FULL  = PROJECT_ROOT . '/schema-examples/full.yml';

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
        isSame(null, $schemaEmpty->getFilenamePattern());

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isSame('/demo(-\d+)?\.csv$/i', $schemaFull->getFilenamePattern());
    }

    public function testScvStruture(): void
    {
        $schemaEmpty = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
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

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
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
        $schemaEmpty = new Schema(self::SCHEMA_EXAMPLE_EMPTY);
        isSame([], $schemaEmpty->getColumns());

        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        isSame([
            0 => 'Column Name (header)',
            1 => 'another_column',
            2 => 'third_column',
            3 => 3,
        ], \array_keys($schemaFull->getColumns()));
    }

    public function testColumnByNameAndId(): void
    {
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
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
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
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
        $schemaFull   = new Schema(self::SCHEMA_EXAMPLE_FULL);
        $columnByName = $schemaFull->getColumn('Column Name (header)');
        $columnById   = $schemaFull->getColumn(0);

        isSame([
            'not_empty'             => true,
            'exact_value'           => 'Some string',
            'allow_values'          => ['y', 'n', ''],
            'regex'                 => '/^[\\d]{2}$/',
            'length'                => 5,
            'length_not'            => 4,
            'length_min'            => 1,
            'length_max'            => 10,
            'is_trimed'             => true,
            'is_lowercase'          => true,
            'is_uppercase'          => true,
            'is_capitalize'         => true,
            'word_count'            => 5,
            'word_count_not'        => 4,
            'word_count_min'        => 1,
            'word_count_max'        => 10,
            'contains'              => 'Hello',
            'contains_one'          => ['a', 'b'],
            'contains_all'          => ['a', 'b', 'c'],
            'starts_with'           => 'prefix ',
            'ends_with'             => ' suffix',
            'num'                   => 5,
            'num_not'               => 4,
            'num_min'               => 1,
            'num_max'               => 10,
            'precision'             => 5,
            'precision_not'         => 4,
            'precision_min'         => 1,
            'precision_max'         => 10,
            'date'                  => '01 Jan 2000',
            'date_not'              => '2006-01-02 15:04:05 -0700 Europe/Rome',
            'date_min'              => '+1 day',
            'date_max'              => 'now',
            'date_format'           => 'Y-m-d',
            'is_date'               => true,
            'is_bool'               => true,
            'is_int'                => true,
            'is_float'              => true,
            'is_ip4'                => true,
            'is_url'                => true,
            'is_email'              => true,
            'is_domain'             => true,
            'is_uuid'               => true,
            'is_alias'              => true,
            'is_latitude'           => true,
            'is_longitude'          => true,
            'is_geohash'            => true,
            'is_cardinal_direction' => true,
            'is_usa_market_name'    => true,
        ], $columnByName->getRules());

        isSame($columnByName->getRules(), $columnById->getRules());
    }

    public function testGetColumnAggregateRules(): void
    {
        $schemaFull = new Schema(self::SCHEMA_EXAMPLE_FULL);
        $column     = $schemaFull->getColumn(0);

        isSame([
            'is_unique' => true,
        ], $column->getAggregateRules());
    }
}
