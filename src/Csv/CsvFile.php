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

namespace JBZoo\CsvBlueprint\Csv;

use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Validators\ValidatorColumn;
use JBZoo\CsvBlueprint\Validators\ValidatorCsv;
use League\Csv\Reader as LeagueReader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

final class CsvFile
{
    private string       $csvFilename;
    private LeagueReader $reader;
    private Schema       $schema;
    private bool         $isEmpty;
    private ?array       $header = null;

    public function __construct(string $csvFilename, null|array|string $csvSchemaFilenameOrArray = null)
    {
        if (\realpath($csvFilename) !== false && \file_exists($csvFilename) === false) {
            throw new Exception('File not found: ' . $csvFilename);
        }

        $this->csvFilename = $csvFilename;
        $this->isEmpty = \filesize($this->csvFilename) <= 1;
        $this->schema = new Schema($csvSchemaFilenameOrArray);
        $this->reader = $this->prepareReader();
    }

    /**
     * Returns the CSV filename.
     * @return string the CSV filename
     */
    public function getCsvFilename(): string
    {
        return $this->csvFilename;
    }

    /**
     * Returns the header array.
     * The header array contains the column names or indexes of the CSV file.
     * If the CSV file has a header row, it will return the column names.
     * If the CSV file does not have a header row, it will return the column indexes.
     * @return string[] the header array
     */
    public function getHeader(): array
    {
        if ($this->header === null) {
            $this->header = [];
            if ($this->schema->csvHasHeader() && !$this->isEmpty) {
                // TODO: add handler for empty file
                // League\Csv\SyntaxError : The header record does not exist or is empty at offset: `0
                $this->header = $this->getRecordsChunk(0, 1)->first();
            } else {
                $this->header = \range(0, \count($this->getRecordsChunk(0, 1)->first()) - 1);
            }
        }

        return $this->header;
    }

    /**
     * Retrieve the records from the CSV reader.
     * @param  null|int  $offset The offset of the column to fetch records from
     * @return \Iterator the iterator of records
     */
    public function getRecords(?int $offset = null): \Iterator
    {
        if ($offset !== null) {
            $records = $this->reader->fetchColumnByOffset($offset);
        } else {
            $records = $this->reader->getRecords();
        }

        return $records;
    }

    /**
     * Retrieves a chunk of records from the TabularDataReader.
     * @param  int               $offset the starting offset of the chunk
     * @param  int               $limit  the maximum number of records to retrieve in the chunk
     * @return TabularDataReader the TabularDataReader object containing the records
     */
    public function getRecordsChunk(int $offset = 0, int $limit = -1): TabularDataReader
    {
        return Statement::create(null, $offset, $limit)->process($this->reader, []); // No headers is required!
    }

    /**
     * @param  bool       $quickStop Whether to stop validation after encountering the first error
     * @return ErrorSuite The error suite containing any validation errors
     */
    public function validate(bool $quickStop = false): ErrorSuite
    {
        return (new ValidatorCsv($this, $this->schema))->validate($quickStop);
    }

    /**
     * Get the number of columns in the real dataset.
     * @return int the total number of columns in the real dataset
     */
    public function getRealColumNumber(): int
    {
        return \count($this->getRecordsChunk(0, 1)->first());
    }

    /**
     * Returns the schema object associated with this instance.
     * @return Schema the schema object
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Retrieves the columns mapped by header.
     * @param  null|ErrorSuite $errors an instance of ErrorSuite to store any errors encountered during mapping
     * @return Column[]        an associative array where the keys represent the CSV header index and the values
     *                         represent the corresponding CSV Column objects
     */
    public function getColumnsMappedByHeader(?ErrorSuite $errors = null): array
    {
        $isHeader = $this->schema->csvHasHeader();

        $map = [];
        $errors ??= new ErrorSuite();

        $realHeader = $this->getHeader();
        foreach ($realHeader as $realIndex => $realColumnName) {
            $realIndex = (int)$realIndex;
            if ($isHeader) {
                $schemaColumn = $this->schema->getColumn($realColumnName);
            } else {
                $schemaColumn = $this->schema->getColumn($realIndex);
            }

            if ($schemaColumn !== null) {
                $schemaColumn->setCsvOffset($realIndex);
                $map[$realIndex] = $schemaColumn;
            }
        }

        if ($this->schema->isAllowExtraColumns()) {
            $unusedSchemaColumns = \array_filter(
                $this->schema->getColumns(),
                static fn ($column) => $column->getCsvOffset() === null,
            );

            foreach ($unusedSchemaColumns as $unusedSchemaColumn) {
                if ($unusedSchemaColumn->isRequired()) {
                    $errors->addError(
                        new Error(
                            'required',
                            'Required column not found in CSV',
                            "Schema Col Id: {$unusedSchemaColumn->getSchemaId()}",
                            ValidatorColumn::FALLBACK_LINE,
                        ),
                    );
                }
            }
        }

        return $map;
    }

    /**
     * Retrieves only the header names from the columns mapped by header.
     * @param  null|ErrorSuite $errors an instance of ErrorSuite to store any errors encountered during mapping
     * @return string[]        an array containing only the names of the columns mapped by header
     */
    public function getColumnsMappedByHeaderNamesOnly(?ErrorSuite $errors = null): array
    {
        return \array_map(static fn (Column $column) => $column->getName(), $this->getColumnsMappedByHeader($errors));
    }

    private function prepareReader(): LeagueReader
    {
        $reader = LeagueReader::createFromPath($this->csvFilename)
            ->setDelimiter($this->schema->getCsvDelimiter())
            ->setEnclosure($this->schema->getCsvEnclosure())
            ->setEscape($this->schema->getCsvQuoteChar())
            ->setHeaderOffset(null); // It's important to set it to null to optimize memory usage!

        if ($this->schema->csvHasBOM()) {
            $reader->includeInputBOM();
        } else {
            $reader->skipInputBOM();
        }

        return $reader;
    }
}
