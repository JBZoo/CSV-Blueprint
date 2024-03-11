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
        if (\realpath($csvFilename) !== false && \file_exists($csvFilename) === false) {
            throw new \InvalidArgumentException('File not found: ' . $csvFilename);
        }

        $this->csvFilename = $csvFilename;
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

    public function getRecords(): \Iterator
    {
        return $this->reader->getRecords($this->getHeader());
    }

    public function getRecordsChunk(int $offset = 0, int $limit = -1): \League\Csv\TabularDataReader
    {
        return Statement::create(null, $offset, $limit)->process($this->reader, $this->getHeader());
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        $errors = new ErrorSuite($this->getCsvFilename());

        $errors->addErrorSuit($this->validateHeader())
            ->addErrorSuit($this->validateEachCell($quickStop))
            ->addErrorSuit(self::validateAggregateRules($quickStop));

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
                    'csv.header',
                    "Property \"<c>name</c>\" is not defined in schema: \"<c>{$this->schema->getFilename()}</c>\"",
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

                $errors->addErrorSuit($column->validate($record[$column->getKey()], (int)$line + 1));
                if ($quickStop && $errors->count() > 0) {
                    return $errors;
                }
            }
        }

        return $errors;
    }

    private static function validateAggregateRules(bool $quickStop = false): ErrorSuite
    {
        $errors = new ErrorSuite();

        if ($quickStop && $errors->count() > 0) {
            return $errors;
        }

        return new ErrorSuite();
    }
}
