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

    public function validate(bool $quickStop = false): ErrorSuite
    {
        $errors = new ErrorSuite();

        $errors->addErrorSuit($this->validateHeader())
            ->addErrorSuit($this->validateEachCell($quickStop))
            ->addErrorSuit($this->validateAggregateRules($quickStop));

        return $errors;
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

    private function validateHeader(): ErrorSuite
    {
        $errors = new ErrorSuite();

        if (!$this->getCsvStructure()->isHeader()) {
            return $errors;
        }

        foreach ($this->schema->getColumns() as $column) {
            if ($column->getName() === '') {
                $error = new Error(
                    'csv_structure.header',
                    "Property \"name\" is not defined in schema: \"{$this->schema->getFilename()}\"",
                    $column->getHumanName(),
                    1,
                );

                $errors->addError($error);
            }
        }

        return $errors;
    }

    private function validateEachCell(bool $quickStop = false): ErrorSuite
    {
        $errors = new ErrorSuite();

        foreach ($this->getRecords() as $line => $record) {
            $columns = $this->schema->getColumnsMappedByHeader($this->getHeader());

            foreach ($columns as $column) {
                if ($column === null) {
                    continue;
                }

                $errors->addErrorSuit($column->validate($record[$column->getKey()], $line + 1));
                if ($quickStop && $errors->count() > 0) {
                    return $errorAcc;
                }
            }
        }

        return $errors;
    }

    private function validateAggregateRules(bool $quickStop = false): ErrorSuite
    {
        return new ErrorSuite();
    }
}
