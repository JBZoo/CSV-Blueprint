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
use League\Csv\ByteSequence;
use League\Csv\Reader as LeagueReader;
use League\Csv\Statement;

final class CsvFile
{
    private string       $csvFilename;
    private ParseConfig  $structure;
    private LeagueReader $reader;
    private Schema       $schema;

    public function __construct(string $csvFilename, null|array|string $csvSchemaFilenameOrArray = null)
    {
        if (\realpath($csvFilename) && \file_exists($csvFilename) === false) {
            throw new \InvalidArgumentException('File not found: ' . $csvFilename);
        }

        $this->csvFilename = \realpath($csvFilename);
        $this->schema      = new Schema($csvSchemaFilenameOrArray);
        $this->structure   = $this->schema->getCsvStructure();
        $this->reader      = $this->prepareReader();
    }

    public function getCsvFilename(): string
    {
        return $this->csvFilename;
    }

    public function getCsvStructure(): ParseConfig
    {
        return $this->structure;
    }

    /**
     * @return string[]
     */
    public function getHeader(): array
    {
        if ($this->structure->isHeader()) {
            return $this->reader->getHeader();
        }

        return [];
    }

    public function getRecords(): \League\Csv\MapIterator
    {
        return $this->reader->getRecords($this->getHeader());
    }

    public function getRecordsChunk(int $offset = 0, int $limit = -1): \League\Csv\ResultSet
    {
        return Statement::create(null, $offset, $limit)->process($this->reader, $this->getHeader());
    }

    public function validate(bool $quickStop = false): array
    {
        return $this->validateHeader() +
            $this->validateEachCell($quickStop) +
            $this->validateAggregateRules($quickStop);
    }

    private function prepareReader(): LeagueReader
    {
        $reader = LeagueReader::createFromPath($this->csvFilename)
            ->setDelimiter($this->structure->getDelimiter())
            ->setEnclosure($this->structure->getEnclosure())
            ->setEscape($this->structure->getQuoteChar())
            ->setHeaderOffset($this->structure->isHeader() ? 0 : null);

        if ($this->structure->isBom()) {
            $reader->includeInputBOM();

            $encoding = $this->structure->getEncoding();
            if ($encoding === CsvStructure::ENCODING_UTF8) {
                $reader->setOutputBOM(ByteSequence::BOM_UTF8);
            } elseif ($encoding === CsvStructure::ENCODING_UTF16) {
                $reader->setOutputBOM(ByteSequence::BOM_UTF16_LE);
            } elseif ($encoding === CsvStructure::ENCODING_UTF32) {
                $reader->setOutputBOM(ByteSequence::BOM_UTF32_LE);
            }
        } else {
            $reader->skipInputBOM();
        }

        return $reader;
    }

    private function validateHeader(): array
    {
        if (!$this->getCsvStructure()->isHeader()) {
            return [];
        }

        $errorAcc = [];

        foreach ($this->schema->getColumns() as $column) {
            if ($column->getName() === '') {
                $errorAcc[] = "Property \"name\" is not defined for column id={$column->getId()} " .
                    "in schema: {$this->schema->getFilename()}";
            }
        }

        return $errorAcc;
    }

    private function validateEachCell(bool $quickStop = false): array
    {
        $errorAcc = [];

        foreach ($this->getRecords() as $line => $record) {
            $columns = $this->schema->getColumnsMappedByHeader($this->getHeader());

            foreach ($columns as $column) {
                if ($column === null) {
                    continue;
                }

                $errorAcc = $this->appendErrors($errorAcc, $column->validate($record[$column->getKey()], $line));
                if ($quickStop && \count($errorAcc) > 0) {
                    return $errorAcc;
                }
            }
        }

        return $errorAcc;
    }

    private function validateAggregateRules(bool $quickStop = false): array
    {
        return [];
    }

    private function appendErrors(array $errorAcc, array $newErrors): array
    {
        $errorAcc += \array_filter($newErrors);

        return $errorAcc;
    }
}
