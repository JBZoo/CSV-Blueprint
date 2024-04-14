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
    private AbstractData $internalState;

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
            $this->internalState = (new SchemaDataPrep($data, $basepath))->buildData();
        } catch (\Exception $e) {
            throw new Exception(
                "Invalid schema \"{$this->getFilename(true)}\" data.\nUnexpected error: \"{$e->getMessage()}\"",
            );
        }

        $this->columns = $this->prepareColumns();
    }

    /**
     * Retrieves the filename associated with the current object.
     * @param  bool   $relativePath determines whether to return the filename with a relative path
     * @return string The filename. Returns 'undefined' if the filename is null, empty, or equal to '_custom_array_'.
     */
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

    /**
     * Finds and returns a column from the specified table schema by its name or index.
     * @param  int|string  $columNameOrId the name or index of the column to retrieve
     * @param  null|string $forceName     the exact name of the column to retrieve when searching by index
     * @return null|Column the matched column object, or null if not found
     */
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

    /**
     * Retrieves the filename pattern from the current object and prepares it as a regex.
     * @return null|string the prepared regex pattern for the filename, or null if no pattern is set
     */
    public function getFilenamePattern(): ?string
    {
        return Utils::prepareRegex($this->internalState->getStringNull('filename_pattern'));
    }

    /**
     * Validates the current schema using a ValidatorSchema object and returns an ErrorSuite object.
     * @param  bool       $quickStop whether to stop validation at the first encountered error
     * @return ErrorSuite the error suite object containing validation errors
     */
    public function validate(bool $quickStop = false): ErrorSuite
    {
        return (new ValidatorSchema($this))->validate($quickStop);
    }

    /**
     * Returns a clone of the internal data object.
     * @return AbstractData a clone of the internal data object
     */
    public function getData(): AbstractData
    {
        return clone $this->internalState; // Clone data to avoid any external side effects.
    }

    /**
     * Returns an array of the column names from the specified table schema.
     * @return array the array of column names
     */
    public function getSchemaHeader(): array
    {
        $schemaColumns = $this->getColumns();
        return \array_reduce($schemaColumns, static function (array $carry, Column $column) {
            $carry[] = $column->getName();
            return $carry;
        }, []);
    }

    /**
     * Checks if the strict column order rule is enabled.
     * @return bool true if the strict column order rule is enabled, false otherwise
     */
    public function isStrictColumnOrder(): bool
    {
        return $this->internalState->findBool('structural_rules.strict_column_order', true);
    }

    /**
     * Checks if the table schema allows extra columns.
     * @return bool true if extra columns are allowed, false otherwise
     */
    public function isAllowExtraColumns(): bool
    {
        return $this->internalState->findBool('structural_rules.allow_extra_columns', false);
    }

    /**
     * Checks if the CSV file has a Byte Order Mark (BOM).
     * @return bool true if the CSV file has a BOM, false otherwise
     */
    public function csvHasBOM(): bool
    {
        return $this->internalState->findBool('csv.bom');
    }

    /**
     * Retrieves the CSV delimiter from the internal state.
     * @return string    the CSV delimiter as a single character
     * @throws Exception if the delimiter is not a single character
     */
    public function getCsvDelimiter(): string
    {
        $value = $this->internalState->findString('csv.delimiter');
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new Exception('Delimiter must be a single character');
    }

    /**
     * Retrieves the CSV quote character from the internal state.
     * @return string    the CSV quote character
     * @throws Exception if the quote char is not a single character
     */
    public function getCsvQuoteChar(): string
    {
        $value = $this->internalState->findString('csv.quote_char');
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new Exception('Quote char must be a single character');
    }

    /**
     * Retrieves the CSV enclosure character from the internal state.
     * @return string    the CSV enclosure character
     * @throws Exception if the enclosure is not a single character
     */
    public function getCsvEnclosure(): string
    {
        $value = $this->internalState->findString('csv.enclosure');

        if (\strlen($value) === 1) {
            return $value;
        }

        throw new Exception('Enclosure must be a single character');
    }

    /**
     * Returns the CSV encoding specified in the internal state.
     *
     * @return string    the CSV encoding. It can be one of the following values:
     *                   - self::ENCODING_UTF8: UTF-8 encoding
     *                   - self::ENCODING_UTF16: UTF-16 encoding
     *                   - self::ENCODING_UTF32: UTF-32 encoding
     * @throws Exception if the specified encoding is not valid
     */
    public function getCsvEncoding(): string
    {
        $encoding = \strtolower(
            \trim($this->internalState->findString('csv.encoding')),
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

    /**
     * Returns whether the CSV file has a header row.
     * @return bool true if the CSV file has a header row, false otherwise
     */
    public function csvHasHeader(): bool
    {
        return $this->internalState->findBool('csv.header');
    }

    /**
     * Retrieves the CSV parameters for the specified table schema.
     */
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

    /**
     * Retrieves the parameters for the structural rules of the table schema.
     */
    public function getStructuralRulesParams(): array
    {
        return [
            'strict_column_order' => $this->isStrictColumnOrder(),
            'allow_extra_columns' => $this->isAllowExtraColumns(),
        ];
    }

    /**
     * Converts the internal state to YAML string representation.
     * @param  bool        $removeDefaultValues whether to remove default values from the dumped data
     * @param  bool        $cliColored          whether to add color formatting for CLI output
     * @param  null|string $basedOnCsv          the CSV file name that the data is based on
     * @return string      the data converted to YAML string
     */
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

        foreach ($this->internalState->getArray('columns') as $columnId => $columnPreferences) {
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
