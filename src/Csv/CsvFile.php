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
use JBZoo\CsvBlueprint\Validators\CsvValidator;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use League\Csv\Reader as LeagueReader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

final class CsvFile
{
    private string       $csvFilename;
    private ParseConfig  $structure;
    private LeagueReader $reader;
    private Schema       $schema;
    private bool         $isEmpty;

    public function __construct(string $csvFilename, null|array|string $csvSchemaFilenameOrArray = null)
    {
        if (\realpath($csvFilename) !== false && \file_exists($csvFilename) === false) {
            throw new \InvalidArgumentException('File not found: ' . $csvFilename);
        }

        $this->csvFilename = $csvFilename;
        $this->isEmpty     = \filesize($this->csvFilename) <= 1;
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
        if ($this->structure->isHeader() && !$this->isEmpty) {
            // TODO: add handler for empty file
            // League\Csv\SyntaxError : The header record does not exist or is empty at offset: `0
            return $this->reader->getHeader();
        }

        return [];
    }

    public function getRecords(): \Iterator
    {
        return $this->reader->getRecords($this->getHeader());
    }

    public function getRecordsChunk(int $offset = 0, int $limit = -1): TabularDataReader
    {
        return Statement::create(null, $offset, $limit)->process($this->reader, $this->getHeader());
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        return (new CsvValidator($this, $this->schema))->validate($quickStop);
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
}
