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
use Symfony\Component\Yaml\Yaml;

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;

final class Schema
{
    public const ENCODING_UTF8 = 'utf-8';
    public const ENCODING_UTF16 = 'utf-16';
    public const ENCODING_UTF32 = 'utf-32';

    /** @var Column[] */
    private array        $columns;
    private ?string      $filename;
    private AbstractData $data;

    public function __construct(null|array|string $csvSchemaFilenameOrArray = null)
    {
        if (\is_array($csvSchemaFilenameOrArray)) {
            $this->filename = '_custom_array_';
            $data = new Data($csvSchemaFilenameOrArray);
        } elseif (
            \is_string($csvSchemaFilenameOrArray)
            && $csvSchemaFilenameOrArray !== ''
            && \file_exists($csvSchemaFilenameOrArray)
        ) {
            $this->filename = $csvSchemaFilenameOrArray;
            $fileExtension = \pathinfo($csvSchemaFilenameOrArray, \PATHINFO_EXTENSION);

            if ($fileExtension === 'yml' || $fileExtension === 'yaml') {
                $data = yml($csvSchemaFilenameOrArray);
            } elseif ($fileExtension === 'json') {
                $data = json($csvSchemaFilenameOrArray);
            } elseif ($fileExtension === 'php') {
                $data = phpArray($csvSchemaFilenameOrArray);
            } else {
                throw new Exception("Unsupported file extension: {$fileExtension}");
            }
        } elseif (\is_string($csvSchemaFilenameOrArray)) {
            throw new Exception("Invalid schema data: {$csvSchemaFilenameOrArray}");
        } else {
            $this->filename = null;
            $data = new Data();
        }

        $basepath = '.';
        $filename = (string)$this->filename;
        if ($filename !== '' && \file_exists($filename)) {
            $this->filename = (string)\realpath($filename);
            $basepath = \dirname($filename);
        }

        try {
            $this->data = (new SchemaDataPrep($data, $basepath))->buildData();
        } catch (\Exception $e) {
            throw new Exception(
                "Invalid schema \"{$this->getFilename(true)}\" data.\nUnexpected error: \"{$e->getMessage()}\"",
            );
        }

        $this->columns = $this->prepareColumns();
    }

    public function getFilename(bool $relativePath = false): string
    {
        if ($this->filename === null || $this->filename === '' || $this->filename === '_custom_array_') {
            return 'undefined';
        }

        return $relativePath ? Utils::cutPath($this->filename) : $this->filename;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(int|string $columNameOrId, ?string $forceName = null): ?Column
    {
        // By "index"
        if (\is_numeric($columNameOrId)) {
            return \array_values($this->getColumns())[$columNameOrId] ?? null;
        }

        // by "index:"
        if (\preg_match('/^(\d+):$/', $columNameOrId, $matches) !== 0) {
            return $this->getColumn((int)$matches[1]);
        }

        // by "index:name"
        if (\preg_match('/^(\d+):(.*)$/', $columNameOrId, $matches) !== 0) {
            return $this->getColumn((int)$matches[1], $matches[2]);
        }

        if ($forceName !== null) {
            // by "index:name" (real)
            foreach ($this->getColumns() as $columnIndex => $schemaColumn) {
                if (
                    $columnIndex === (int)$columNameOrId
                    && $schemaColumn->getName() === $forceName
                ) {
                    return $schemaColumn;
                }
            }
        } else {
            // by "name"
            foreach ($this->getColumns() as $schemaColumn) {
                if ($schemaColumn->getName() === $columNameOrId) {
                    return $schemaColumn;
                }
            }
        }

        return null;
    }

    public function getFilenamePattern(): ?string
    {
        return Utils::prepareRegex($this->data->getStringNull('filename_pattern'));
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
        return $this->data->findBool('csv.bom');
    }

    public function getCsvDelimiter(): string
    {
        $value = $this->data->findString('csv.delimiter');
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new Exception('Delimiter must be a single character');
    }

    public function getCsvQuoteChar(): string
    {
        $value = $this->data->findString('csv.quote_char');
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new Exception('Quote char must be a single character');
    }

    public function getCsvEnclosure(): string
    {
        $value = $this->data->findString('csv.enclosure');

        if (\strlen($value) === 1) {
            return $value;
        }

        throw new Exception('Enclosure must be a single character');
    }

    public function getCsvEncoding(): string
    {
        $encoding = \strtolower(
            \trim($this->data->findString('csv.encoding')),
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

        throw new Exception("Invalid encoding: {$encoding}");
    }

    public function csvHasHeader(): bool
    {
        return $this->data->findBool('csv.header');
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

    public function dumpAsYamlString(
        bool $removeDefaultValues = true,
        bool $cliColored = false,
        ?string $basedOnCsv = null,
    ): string {
        $dump = $this->getData()->getArrayCopy();
        if ($removeDefaultValues) {
            $dump = Utils::removeDefaultSettings($dump, self::getDefaultValues(\count($dump['columns'])));
        }

        $ymlAsString = Yaml::dump(
            $dump,
            10,
            2,
            Yaml::DUMP_NULL_AS_TILDE
            | Yaml::DUMP_NUMERIC_KEY_AS_STRING
            | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
            | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE
            | Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE,
        );

        // Fix formating
        $ymlAsString = \str_replace(
            ["  -\n    ", "columns:\n"],
            ["\n  - ", 'columns:'],
            $ymlAsString,
        );

        if ($basedOnCsv !== null) {
            $ymlAsString = "# Based on CSV \"{$basedOnCsv}\"\n{$ymlAsString}";
        }

        if ($this->getFilename(true) !== 'undefined') {
            $ymlAsString = "# Schema file is \"{$this->getFilename(true)}\"\n{$ymlAsString}";
        }

        if ($cliColored) {
            $ymlAsString = (string)\preg_replace('/^([ \t]*)([^:\n]+:)/m', '$1<c>$2</c>', $ymlAsString); // keys
            $ymlAsString = (string)\preg_replace('/^(#.+)/m', '<gray>$1</gray>', $ymlAsString); // comments

            $ymlAsString = \implode("\n", [
                '<blue>```yaml</blue>',
                $ymlAsString,
                '<blue>```</blue>',
            ]);
        }

        return \trim($ymlAsString) . "\n";
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

    private static function getDefaultValues(int $numberOfColumns = 0): array
    {
        $default = (new self())->getData()->getArrayCopy();

        for ($i = 0; $i < $numberOfColumns; $i++) {
            $default['columns'][] = SchemaDataPrep::DEFAULTS['column'];
        }

        return $default;
    }
}
