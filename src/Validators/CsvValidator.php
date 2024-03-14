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

namespace JBZoo\CsvBlueprint\Validators;

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;

final class CsvValidator
{
    private CsvFile    $csv;
    private ErrorSuite $errors;
    private Schema     $schema;

    public function __construct(CsvFile $csv, Schema $schema)
    {
        $this->csv    = $csv;
        $this->schema = $schema;
        $this->errors = new ErrorSuite($this->csv->getCsvFilename());
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        return $this->errors
            ->addErrorSuit($this->validateFile($quickStop))
            ->addErrorSuit($this->validateHeader($quickStop))
            ->addErrorSuit($this->validateLines($quickStop));
    }

    private function validateHeader(bool $quickStop = false): ErrorSuite
    {
        $errors = new ErrorSuite();

        if (!$this->schema->getCsvStructure()->isHeader()) {
            return $errors;
        }

        foreach ($this->schema->getColumns() as $column) {
            if ($column->getName() === '') {
                $error = new Error(
                    'csv.header',
                    'Property "<c>name</c>" is not defined in schema: ' .
                    "\"<c>{$this->schema->getFilename()}</c>\"",
                    $column->getHumanName(),
                    ColumnValidator::FALLBACK_LINE,
                );

                $errors->addError($error);
            }

            if ($quickStop && $errors->count() > 0) {
                return $errors;
            }
        }

        return $errors;
    }

    private function validateLines(bool $quickStop = false): ErrorSuite
    {
        $errors  = new ErrorSuite();
        $columns = $this->schema->getColumnsMappedByHeader($this->csv->getHeader());

        foreach ($columns as $column) {
            $columValues = [];
            if ($column === null) {
                continue;
            }

            foreach ($this->csv->getRecords() as $line => $record) {
                $columValues[] = $record[$column->getKey()];
                $errors->addErrorSuit($column->validateCell($record[$column->getKey()], (int)$line + 1));
                if ($quickStop && $errors->count() > 0) {
                    return $errors;
                }
            }

            $errors->addErrorSuit($column->validateList($columValues));
        }

        return $errors;
    }

    private function validateFile(bool $quickStop = false): ErrorSuite
    {
        $errors = new ErrorSuite();

        $filenamePattern = $this->schema->getFilenamePattern();
        if (
            $filenamePattern !== null
            && $filenamePattern !== ''
            && \preg_match($filenamePattern, $this->csv->getCsvFilename()) === 0
        ) {
            $error = new Error(
                'filename_pattern',
                'Filename "<c>' . Utils::cutPath($this->csv->getCsvFilename()) .
                "</c>\" does not match pattern: \"<c>{$filenamePattern}</c>\"",
                '',
                ColumnValidator::FALLBACK_LINE,
            );

            $errors->addError($error);

            if ($quickStop && $errors->count() > 0) {
                return $errors;
            }
        }

        return $errors;
    }
}
