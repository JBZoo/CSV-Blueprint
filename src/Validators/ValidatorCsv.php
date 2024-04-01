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

        if (!$this->schema->getCsvParserConfig()->isHeader()) {
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

        if ($this->schema->isStrictColumnOrder()) {
            $realColumns = $this->csv->getHeader();
            $schemaColumns = $this->schema->getSchemaHeader();

            if (!Utils::isArrayInOrder($schemaColumns, $realColumns)) {
                $error = new Error(
                    'strict_column_order',
                    "Real columns order doesn't match schema. " .
                    'Expected: <c>' . Utils::printList($realColumns) . '</c>. ' .
                    'Actual: <green>' . Utils::printList($schemaColumns) . '</green>',
                    '',
                    ValidatorColumn::FALLBACK_LINE,
                );

                $errors->addError($error);
                if ($quickStop && $errors->count() > 0) {
                    return $errors;
                }
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
        $mappedColumns = $this->csv->getColumnsMappedByHeader($errors);
        $isHeaderEnabled = $this->schema->getCsvParserConfig()->isHeader();

        foreach ($mappedColumns as $columnIndex => $column) {
            $columnIndex = (int)$columnIndex;
            $messPrefix = "<i>Column</i> \"{$column->getHumanName()}\" -"; // System message prefix. Debug only!

            $columValues = new \Ds\Vector();

            Utils::debug("{$messPrefix} Column start");
            $colValidator = $column->getValidator();

            Utils::debug("{$messPrefix} Validator created");

            $isAggRules = \count($column->getAggregateRules()) > 0;
            $isRules = \count($column->getRules()) > 0;
            if ($isAggRules) {
                $aggInputType = $colValidator->getAggregationInputType();
                Utils::debug("{$messPrefix} Aggregation Flag: {$aggInputType}");
            } else {
                $aggInputType = AbstarctRule::INPUT_TYPE_UNDEF;
            }

            if (!$isAggRules && !$isRules) { // Time optimization
                Utils::debug("{$messPrefix} Skipped (no rules)");
                continue;
            }

            $lineCounter = 0;
            $startTimer = \microtime(true);
            foreach ($this->csv->getRecords() as $line => $record) {
                if ($isHeaderEnabled && $line === 0) {
                    continue;
                }

                $lineCounter++;
                $lineNum = (int)$line + 1;

                if ($isRules) { // Time optimization
                    if (!isset($record[$columnIndex])) {
                        $errors->addError( // Something really went wrong. See debug getColumnsMappedByHeader().
                            new Error(
                                'csv.column',
                                "Column index:{$columnIndex} not found",
                                $column->getHumanName(),
                                $lineNum,
                            ),
                        );
                    } else {
                        $errors->addErrorSuit($colValidator->validateCell($record[$columnIndex], $lineNum));
                    }

                    if ($quickStop && $errors->count() > 0) {
                        return $errors;
                    }
                }

                if ($isAggRules && isset($record[$columnIndex])) {  // Time & memory optimization
                    $columValues[] = ValidatorColumn::prepareValue($record[$columnIndex], $aggInputType);
                }
            }
            Utils::debug("{$messPrefix} Lines <yellow>" . \number_format($lineCounter) . '</yellow>');
            Utils::debugSpeed("{$messPrefix} Cell - ", $lineCounter, $startTimer);

            if ($isAggRules) { // Time optimization
                $startTimerAgg = \microtime(true);
                $errors->addErrorSuit($colValidator->validateList($columValues, $lineCounter));
                Utils::debugSpeed("{$messPrefix} Agg - ", $lineCounter, $startTimerAgg);
            }

            Utils::debugSpeed("{$messPrefix} Total - ", $lineCounter, $startTimer);
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

        if (!$this->schema->isAllowExtraColumns()) {
            if ($this->schema->getCsvParserConfig()->isHeader()) {
                $realColumns = $this->csv->getHeader();
                $schemaColumns = $this->schema->getSchemaHeader();

                $notFoundColums = \array_filter( // Filter to exclude duplicate error. See test testCellRuleNoName
                    \array_diff($schemaColumns, $realColumns),
                    static fn ($name) => $name !== '',
                );

                if (\count($notFoundColums) > 0) {
                    $error = new Error(
                        'allow_extra_columns',
                        'Column(s) not found in CSV: ' . Utils::printList($notFoundColums, 'c'),
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
                        'allow_extra_columns',
                        "Schema number of columns \"<c>{$schemaColumns}</c>\" greater " .
                        "than real \"<green>{$realColumns}</green>\"",
                        '',
                        ValidatorColumn::FALLBACK_LINE,
                    );

                    $errors->addError($error);
                    if ($quickStop) {
                        return $errors;
                    }
                }
            }
        }

        return $errors;
    }
}
