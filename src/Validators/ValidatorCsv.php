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
use JBZoo\CsvBlueprint\Rules\AbstarctRule;
use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;

final class ValidatorCsv
{
    private CsvFile    $csv;
    private ErrorSuite $errors;
    private Schema     $schema;

    public function __construct(CsvFile $csv, Schema $schema)
    {
        $this->csv = $csv;
        $this->schema = $schema;
        $this->errors = new ErrorSuite($this->csv->getCsvFilename());
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        $errors = $this->validateFile($quickStop);
        if ($errors->count() > 0) {
            $this->errors->addErrorSuit($errors);
            if ($quickStop) {
                return $this->errors;
            }
        }

        $errors = $this->validateHeader($quickStop);
        if ($errors->count() > 0) {
            $this->errors->addErrorSuit($errors);
            if ($quickStop) {
                return $this->errors;
            }
        }

        $errors = $this->validateColumn($quickStop);
        if ($errors->count() > 0) {
            $this->errors->addErrorSuit($errors);
            if ($quickStop) {
                return $this->errors;
            }
        }

        $errors = $this->validateLines($quickStop);
        if ($errors->count() > 0) {
            $this->errors->addErrorSuit($errors);
            if ($quickStop) {
                return $this->errors;
            }
        }

        return $this->errors;
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
                    ValidatorColumn::FALLBACK_LINE,
                );

                $errors->addError($error);
            }

            if ($quickStop && $errors->count() > 0) {
                return $errors;
            }
        }

        return $errors;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function validateLines(bool $quickStop = false): ErrorSuite
    {
        $errors = new ErrorSuite();
        $realColumns = $this->schema->getColumnsMappedByHeader($this->csv->getHeader());

        foreach ($realColumns as $column) {
            $columValues = [];
            if ($column === null) {
                continue;
            }

            $messPrefix = "<i>Column</i> \"{$column->getHumanName()}\" -";

            Utils::debug("{$messPrefix} Column start");
            $colValidator = $column->getValidator();

            Utils::debug("{$messPrefix} Validator created");

            $isAggRules = \count($column->getAggregateRules()) > 0;
            $isRules = \count($column->getRules()) > 0;
            $aggInputType = $isAggRules ? $colValidator->getAggregationInputType() : AbstarctRule::INPUT_TYPE_UNDEF;

            Utils::debug("{$messPrefix} Aggregation Flag: {$aggInputType}");

            if (!$isAggRules && !$isRules) { // Time optimization
                Utils::debug("{$messPrefix} Skipped (no rules)");
                continue;
            }

            $lineCounter = 0;
            $startTimer = \microtime(true);
            foreach ($this->csv->getRecords() as $line => $record) {
                $lineCounter++;
                $lineNum = (int)$line + 1;

                if ($isRules) { // Time optimization
                    if (!isset($record[$column->getKey()])) {
                        $errors->addError(
                            new Error(
                                'csv.column',
                                "Column index:{$column->getKey()} not found",
                                $column->getHumanName(),
                                $lineNum,
                            ),
                        );
                    } else {
                        $errors->addErrorSuit($colValidator->validateCell($record[$column->getKey()], $lineNum));
                    }

                    if ($quickStop && $errors->count() > 0) {
                        return $errors;
                    }
                }

                if ($isAggRules && isset($record[$column->getKey()])) {  // Time & memory optimization
                    $columValues[] = ValidatorColumn::prepareValue($record[$column->getKey()], $aggInputType);
                }
            }
            Utils::debug("{$messPrefix} <yellow>Lines</yellow> {$lineCounter}");
            Utils::debug(
                "{$messPrefix} <yellow>Speed:cell</yellow> "
                . \number_format($lineCounter / (\microtime(true) - $startTimer)) . ' lines/sec',
            );

            if ($isAggRules) { // Time optimization
                $errors->addErrorSuit($colValidator->validateList($columValues, $lineCounter));
            }

            Utils::debug("{$messPrefix} Column finished");
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
            && Utils::testRegex($filenamePattern, $this->csv->getCsvFilename())
        ) {
            $error = new Error(
                'filename_pattern',
                'Filename "<c>' . Utils::cutPath($this->csv->getCsvFilename()) . '</c>" ' .
                "does not match pattern: \"<c>{$filenamePattern}</c>\"",
                '',
                Error::UNDEFINED_LINE,
            );

            $errors->addError($error);

            if ($quickStop && $errors->count() > 0) {
                return $errors;
            }
        }

        return $errors;
    }

    private function validateColumn(bool $quickStop): ErrorSuite
    {
        $errors = new ErrorSuite();

        if ($this->schema->getCsvStructure()->isHeader()) {
            $realColumns = $this->schema->getColumnsMappedByHeader($this->csv->getHeader());
            $schemaColumns = $this->schema->getColumns();

            $notFoundColums = \array_diff(\array_keys($schemaColumns), \array_keys($realColumns));

            if (\count($notFoundColums) > 0) {
                $error = new Error(
                    'csv.header',
                    'Columns not found in CSV: ' . Utils::printList($notFoundColums, 'c'),
                    '',
                    ValidatorColumn::FALLBACK_LINE,
                );

                $errors->addError($error);
                if ($quickStop) {
                    return $errors;
                }
            }
        } else {
            $schemaColumns = \count($this->schema->getColumns());
            $realColumns = $this->csv->getRealColumNumber();
            if ($realColumns < $schemaColumns) {
                $error = new Error(
                    'csv.header',
                    'Real number of columns is less than schema: ' . $realColumns . ' < ' . $schemaColumns,
                    '',
                    ValidatorColumn::FALLBACK_LINE,
                );

                $errors->addError($error);
                if ($quickStop) {
                    return $errors;
                }
            }
        }

        return $errors;
    }
}
