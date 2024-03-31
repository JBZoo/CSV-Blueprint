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
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Validators\ValidatorCsv;
use League\Csv\Reader as LeagueReader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

final class CsvFile
{
    private string          $csvFilename;
    private CsvParserConfig $csvParserConfig;
    private LeagueReader    $reader;
    private Schema          $schema;
    private bool            $isEmpty;
    private ?array          $header = null;

    public function __construct(string $csvFilename, null|array|string $csvSchemaFilenameOrArray = null)
    {
        if (\realpath($csvFilename) !== false && \file_exists($csvFilename) === false) {
            throw new \InvalidArgumentException('File not found: ' . $csvFilename);
        }

        $this->csvFilename = $csvFilename;
        $this->isEmpty = \filesize($this->csvFilename) <= 1;
        $this->schema = new Schema($csvSchemaFilenameOrArray);
        $this->csvParserConfig = $this->schema->getCsvParserConfig();
        $this->reader = $this->prepareReader();
    }

    public function getCsvFilename(): string
    {
        return $this->csvFilename;
    }

    public function getCsvStructure(): CsvParserConfig
    {
        return $this->csvParserConfig;
    }

    /**
     * @return string[]
     */
    public function getHeader(): array
    {
        if ($this->header === null) {
            $this->header = [];
            if ($this->csvParserConfig->isHeader() && !$this->isEmpty) {
                // TODO: add handler for empty file
                // League\Csv\SyntaxError : The header record does not exist or is empty at offset: `0
                $this->header = $this->getRecordsChunk(0, 1)->first();
            } else {
                $this->header = \range(0, \count($this->getRecordsChunk(0, 1)->first()) - 1);
            }
        }

        return $this->header;
    }

    public function getRecords(?int $offset = null): \Iterator
    {
        if ($offset !== null) {
            $records = $this->reader->fetchColumnByOffset($offset);
        } else {
            $records = $this->reader->getRecords();
        }

        return $records;
    }

    public function getRecordsChunk(int $offset = 0, int $limit = -1): TabularDataReader
    {
        return Statement::create(null, $offset, $limit)->process($this->reader, []); // No headers is required!
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        return (new ValidatorCsv($this, $this->schema))->validate($quickStop);
    }

    public function getRealColumNumber(): int
    {
        return \count($this->getRecordsChunk(0, 1)->first());
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return Column[]
     */
    public function getColumnsMappedByHeader(): array
    {
        $isHeader = $this->schema->getCsvParserConfig()->isHeader();

        $map = [];

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

        return $map;
    }

    private function prepareReader(): LeagueReader
    {
        $reader = LeagueReader::createFromPath($this->csvFilename)
            ->setDelimiter($this->csvParserConfig->getDelimiter())
            ->setEnclosure($this->csvParserConfig->getEnclosure())
            ->setEscape($this->csvParserConfig->getQuoteChar())
            ->setHeaderOffset(null); // It's important to set it to null to optimize memory usage!

        if ($this->csvParserConfig->isBom()) {
            $reader->includeInputBOM();
        } else {
            $reader->skipInputBOM();
        }

        return $reader;
    }
}
