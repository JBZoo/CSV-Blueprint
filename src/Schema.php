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
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Validators\ValidatorSchema;
use JBZoo\Data\AbstractData;
use JBZoo\Data\Data;

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;

final class Schema
{
    public const ENCODING_UTF8 = 'utf-8';
    public const ENCODING_UTF16 = 'utf-16';
    public const ENCODING_UTF32 = 'utf-32';

    private const FALLBACK_VALUES = [
        'csv' => [
            'inherit'    => null,
            'header'     => true,
            'delimiter'  => ',',
            'quote_char' => '\\',
            'enclosure'  => '"',
            'encoding'   => 'utf-8',
            'bom'        => false,
        ],

        'structural_rules' => [
            'strict_column_order' => true,
            'allow_extra_columns' => false,
        ],
    ];

    /** @var Column[] */
    private array        $columns;
    private string       $basepath = '.';
    private ?string      $filename;
    private AbstractData $data;

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

        if ((string)$this->filename !== '') {
            $this->basepath = \dirname((string)$this->filename);
        }

        $this->columns = $this->prepareColumns();
    }

    public function getFilename(): ?string
    {
        return $this->filename;
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

        foreach ($this->data->getArray('includes') as $alias => $includedPath) {
            if (\file_exists($includedPath)) {
                $path = $includedPath;
            } else {
                $path = $this->basepath . \DIRECTORY_SEPARATOR . $includedPath;
            }

            $result[$alias] = new self($path);
        }

        return $result;
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        return (new ValidatorSchema($this))->validate($quickStop);
    }

    public function getData(): AbstractData
    {
        return clone $this->data; // Clone data to avoid any external side effects.
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

    public function csvHasBOM(): bool
    {
        return $this->data->findBool('csv.bom', self::FALLBACK_VALUES['csv']['bom']);
    }

    public function getCsvDelimiter(): string
    {
        $value = $this->data->findString('csv.delimiter', self::FALLBACK_VALUES['csv']['delimiter']);
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new \InvalidArgumentException('Delimiter must be a single character');
    }

    public function getCsvQuoteChar(): string
    {
        $value = $this->data->findString('csv.quote_char', self::FALLBACK_VALUES['csv']['quote_char']);
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new \InvalidArgumentException('Quote char must be a single character');
    }

    public function getCsvEnclosure(): string
    {
        $value = $this->data->findString('csv.enclosure', self::FALLBACK_VALUES['csv']['enclosure']);

        if (\strlen($value) === 1) {
            return $value;
        }

        throw new \InvalidArgumentException('Enclosure must be a single character');
    }

    public function getCsvEncoding(): string
    {
        $encoding = \strtolower(
            \trim($this->data->findString('csv.encoding', self::FALLBACK_VALUES['csv']['encoding'])),
        );

        $availableOptions = [ // TODO: add flexible handler for this
            self::ENCODING_UTF8,
            self::ENCODING_UTF16,
            self::ENCODING_UTF32,
        ];

        $result = \in_array($encoding, $availableOptions, true) ? $encoding : null;
        if ($result !== null) {
            return $result;
        }

        throw new \InvalidArgumentException("Invalid encoding: {$encoding}");
    }

    public function csvHasHeader(): bool
    {
        return $this->data->findBool('csv.header', self::FALLBACK_VALUES['csv']['header']);
    }

    public function getCsvParams(): array
    {
        return [
            'header'     => $this->csvHasHeader(),
            'delimiter'  => $this->getCsvDelimiter(),
            'quote_char' => $this->getCsvQuoteChar(),
            'enclosure'  => $this->getCsvEnclosure(),
            'encoding'   => $this->getCsvEncoding(),
            'bom'        => $this->csvHasBOM(),
        ];
    }

    public function getStructuralRulesParams(): array
    {
        return [
            'strict_column_order' => $this->isStrictColumnOrder(),
            'allow_extra_columns' => $this->isAllowExtraColumns(),
        ];
    }

    /**
     * @return Column[]
     */
    private function prepareColumns(): array
    {
        $result = [];

        foreach ($this->data->getArray('columns') as $columnId => $columnPreferences) {
            $column = new Column((int)$columnId, $columnPreferences);

            $result[$column->getSchemaId()] = $column;
        }

        return $result;
    }
}
