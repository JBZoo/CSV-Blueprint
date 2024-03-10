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
        } elseif (\file_exists($csvSchemaFilenameOrArray)) {
            $this->filename = $csvSchemaFilenameOrArray;
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
        }

        $this->columns = $this->prepareColumns();
    }

    public function getFilename(): string
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
     * @return Column[]
     */
    public function getColumnsMappedByHeader(array $header): array
    {
        $map = [];

        foreach ($header as $headerName) {
            $column           = $this->columns[$headerName] ?? null;
            $map[$headerName] = $column;
        }

        return $map;
    }

    public function getColumn(int|string $columNameOrId)
    {
        if (\is_int($columNameOrId)) {
            $column = \array_values($this->getColumns())[$columNameOrId] ?? null;
        } else {
            $column = $this->getColumns()[$columNameOrId] ?? null;
        }

        if (!$column) {
            throw new Exception("Column \"{$columNameOrId}\" not found in schema \"{$this->filename}\"");
        }

        return $column;
    }

    public function getFinenamePattern(): ?string
    {
        return $this->data->getStringNull('finename_pattern');
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

    /**
     * @return Column[]
     */
    private function prepareColumns(): array
    {
        $result = [];

        foreach ($this->data->getArray('columns') as $columnId => $columnPreferences) {
            $column = new Column($columnId, $columnPreferences);

            $result[$column->getKey()] = $column;
        }

        return $result;
    }
}
