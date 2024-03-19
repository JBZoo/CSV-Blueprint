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
use JBZoo\CsvBlueprint\Csv\ParseConfig;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
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
            $this->data     = new Data($csvSchemaFilenameOrArray);
        } elseif (
            \is_string($csvSchemaFilenameOrArray)
            && $csvSchemaFilenameOrArray !== ''
            && \file_exists($csvSchemaFilenameOrArray)
        ) {
            $this->filename = $csvSchemaFilenameOrArray;
            $this->data     = new Data();
            $fileExtension  = \pathinfo($csvSchemaFilenameOrArray, \PATHINFO_EXTENSION);

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
            $this->data     = new Data();
        }

        $this->columns = $this->prepareColumns();
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getCsvStructure(): ParseConfig
    {
        return new ParseConfig($this->data->getArray('csv'));
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return Column[]|null[]
     * @phan-suppress PhanPartialTypeMismatchReturn
     */
    public function getColumnsMappedByHeader(array $header): array
    {
        $map = [];

        foreach ($header as $headerName) {
            $map[$headerName] = $this->columns[$headerName] ?? null;
        }

        return $map;
    }

    public function getColumn(int|string $columNameOrId): ?Column
    {
        if (\is_int($columNameOrId)) {
            $column = \array_values($this->getColumns())[$columNameOrId] ?? null;
        } else {
            $column = $this->getColumns()[$columNameOrId] ?? null;
        }

        if ($column === null) {
            throw new Exception("Column \"{$columNameOrId}\" not found in schema \"{$this->filename}\"");
        }

        return $column;
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
            $alias      = \trim($alias);

            $result[$alias] = $schemaPath;
        }

        return $result;
    }

    public function validate(): ErrorSuite
    {
        $expected = phpArray(__DIR__ . '/../schema-examples/full.php');

        $expectedColumn = $expected->find('columns.0');
        $expectedMeta   = $expected->remove('columns')->getArrayCopy();

        $actual        = clone $this->data; // We are going to modify the data. No external side effects.
        $actualColumns = $actual->findSelf('columns');
        $actualMeta    = $actual->remove('columns');

        $errors = new ErrorSuite($this->filename);

        $metaErrors = Utils::compareArray($expectedMeta, $actualMeta->getArrayCopy(), 'meta');

        // Validate meta info
        foreach ($metaErrors as $metaError) {
            $errors->addError(new Error('schema', $metaError[1], $metaError[0]));
        }

        // Validate each columns
        foreach ($actualColumns->getArrayCopy() as $columnKey => $actualColumn) {
            $columnId = "{$columnKey}:" . ($actualColumn['name'] ?? '');

            // Validate column names
            if (
                $this->getCsvStructure()->isHeader()
                && (!isset($actualColumn['name']) || $actualColumn['name'] === '')
            ) {
                $errors->addError(
                    new Error(
                        'schema',
                        'The key "<c>name</c>" must be non-empty because the option "<green>csv.header</green>" = true',
                        $columnId,
                    ),
                );
            }

            // Validate column schema
            $columnErrors = Utils::compareArray(
                $expectedColumn,
                $actualColumn,
                $columnId,
                "columns.{$columnKey}",
            );

            foreach ($columnErrors as $columnError) {
                $errors->addError(new Error('schema', $columnError[1], $columnError[0]));
            }
        }

        return $errors;
    }

    /**
     * @return Column[]
     */
    private function prepareColumns(): array
    {
        $result = [];

        foreach ($this->data->getArray('columns') as $columnId => $columnPreferences) {
            $column = new Column((int)$columnId, $columnPreferences);

            $result[$column->getKey()] = $column;
        }

        return $result;
    }
}
