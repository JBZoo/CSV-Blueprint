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

namespace JBZoo\CsvBlueprint;

use JBZoo\CsvBlueprint\Csv\Column;
use JBZoo\CsvBlueprint\Csv\CsvParserConfig;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Validators\ValidatorSchema;
use JBZoo\Data\AbstractData;
use JBZoo\Data\Data;

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;

final class Schema
{
    private ?string      $filename;
    private AbstractData $data;

    /** @var Column[] */
    private array $columns;

    public function __construct(null|array|string $csvSchemaFilenameOrArray = null)
    {
        if (\is_array($csvSchemaFilenameOrArray)) {
            $this->filename = '_custom_array_';
            $this->data = new Data($csvSchemaFilenameOrArray);
        } elseif (
            \is_string($csvSchemaFilenameOrArray)
            && $csvSchemaFilenameOrArray !== ''
            && \file_exists($csvSchemaFilenameOrArray)
        ) {
            $this->filename = $csvSchemaFilenameOrArray;
            $this->data = new Data();
            $fileExtension = \pathinfo($csvSchemaFilenameOrArray, \PATHINFO_EXTENSION);

            if ($fileExtension === 'yml' || $fileExtension === 'yaml') {
                $this->data = yml($csvSchemaFilenameOrArray);
            } elseif ($fileExtension === 'json') {
                $this->data = json($csvSchemaFilenameOrArray);
            } elseif ($fileExtension === 'php') {
                $this->data = phpArray($csvSchemaFilenameOrArray);
            } else {
                throw new \InvalidArgumentException("Unsupported file extension: {$fileExtension}");
            }
        } elseif (\is_string($csvSchemaFilenameOrArray)) {
            throw new \InvalidArgumentException("Invalid schema data: {$csvSchemaFilenameOrArray}");
        } else {
            $this->filename = null;
            $this->data = new Data();
        }

        $this->columns = $this->prepareColumns();
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getCsvParserConfig(): CsvParserConfig
    {
        return new CsvParserConfig($this->data->getArray('csv'));
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(int|string $columNameOrId): ?Column
    {
        if (\is_int($columNameOrId)) {
            return \array_values($this->getColumns())[$columNameOrId] ?? null;
        }

        foreach ($this->getColumns() as $schemaColumn) {
            if ($schemaColumn->getName() === $columNameOrId) {
                return $schemaColumn;
            }
        }

        return null;
    }

    public function getFilenamePattern(): ?string
    {
        return Utils::prepareRegex($this->data->getStringNull('filename_pattern'));
    }

    public function getIncludes(): array
    {
        $result = [];

        foreach ($this->data->getArray('includes') as $includedPath) {
            [$schemaPath, $alias] = \explode(' as ', $includedPath);

            $schemaPath = \trim($schemaPath);
            $alias = \trim($alias);

            $result[$alias] = $schemaPath;
        }

        return $result;
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        return (new ValidatorSchema($this))->validate($quickStop);
    }

    /**
     * Clone data to avoid any external side effects.
     */
    public function getData(): AbstractData
    {
        return clone $this->data;
    }

    public function getSchemaHeader(): array
    {
        $schemaColumns = $this->getColumns();
        return \array_reduce($schemaColumns, static function (array $carry, Column $column) {
            $carry[] = $column->getName();
            return $carry;
        }, []);
    }

    public function isStrictColumnOrder(): bool
    {
        return $this->data->findBool('structural_rules.strict_column_order', true);
    }

    public function isAllowExtraColumns(): bool
    {
        return $this->data->findBool('structural_rules.allow_extra_columns', false);
    }

    /**
     * @return Column[]
     */
    private function prepareColumns(): array
    {
        $result = [];

        foreach ($this->data->getArray('columns') as $columnId => $columnPreferences) {
            $column = new Column($columnId, $columnPreferences);

            $result[$column->getSchemaId()] = $column;
        }

        return $result;
    }
}
