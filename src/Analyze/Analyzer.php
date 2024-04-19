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

namespace JBZoo\CsvBlueprint\Analyze;

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;
use Symfony\Component\Finder\Finder;

final class Analyzer
{
    /**
     * Analyzes a CSV file and suggests parameters for parsing and creating a schema.
     * @param  null|bool $forceHeader Whether to force the presence of a header row. Null to auto-detect.
     * @param  int       $lineLimit   number of lines to read when detecting parameters
     * @return Schema    the suggested schema for the CSV file
     */
    public static function analyzeCsv(string $csvFilename, ?bool $forceHeader, int $lineLimit): Schema
    {
        $suggestedSchema = [
            'name'        => 'Schema for ' . \basename($csvFilename),
            'description' => \implode("\n", [
                'CSV file ' . Utils::cutPath($csvFilename),
                "Suggested schema based on the first {$lineLimit} lines.",
                'Please REVIEW IT BEFORE using.',
            ]),
            'filename_pattern' => self::suggestFilenamePattern($csvFilename),
            'csv'              => self::autoDetectCsvParams($csvFilename, $forceHeader, $lineLimit),
            'columns'          => [],
        ];

        $csv = new CsvFile($csvFilename, ['csv' => $suggestedSchema['csv']]);
        $hasHeader = $csv->getSchema()->csvHasHeader();
        $columns = $hasHeader ? $csv->getHeader() : \range(0, $csv->getRealColumNumber() - 1);

        $suggestedSchema = self::analyzeColumns($columns, $csv, $hasHeader, $lineLimit, $suggestedSchema);

        return new Schema($suggestedSchema);
    }

    private static function analyzeColumnAggregateRules(array $columnValues, int $columnId): array
    {
        $validRules = [];

        /** @var class-string<\JBZoo\CsvBlueprint\Rules\AbstractRule> $ruleClassname */
        foreach (self::getRuleClasses('Aggregate', 'aggregate_rules') as $ruleCode => $ruleClassname) {
            $ruleCode = \str_replace('combo_', '', $ruleCode);

            if ($columnId > 0 && $ruleCode === 'count') { // Only one time rule is enough
                continue;
            }

            try {
                $result = $ruleClassname::analyzeColumnValues($columnValues);
            } catch (\Throwable) {
                $result = false;
            }

            if ($result !== false) {
                if (\is_float($result) && (\is_infinite($result) || \is_nan($result))) {
                    continue;
                }

                $validRules[$ruleCode] = $result;
            }
        }

        return $validRules;
    }

    private static function analyzeColumnRules(array $columnValues): array
    {
        $validRules = [];

        /** @var class-string<\JBZoo\CsvBlueprint\Rules\AbstractRule> $ruleClassname */
        foreach (self::getRuleClasses('Cell', 'rules') as $ruleCode => $ruleClassname) {
            $result = $ruleClassname::analyzeColumnValues($columnValues);

            // Always add `not_empty` rule to make it explicit.
            if ($ruleCode === 'not_empty') {
                $validRules['not_empty'] = $result;
                continue;
            }

            if ($result !== false) {
                if (\is_array($result) && \str_contains($ruleCode, 'combo_')) {
                    foreach ($result as $subKey => $subValue) {
                        $comboRuleCode = \trim(\str_replace('combo_', '', $ruleCode) . '_' . $subKey, '_');
                        $validRules[$comboRuleCode] = $subValue;
                    }
                } else {
                    $validRules[$ruleCode] = $result;
                }
            }
        }

        return $validRules;
    }

    private static function analyzeColumns(
        array $columns,
        CsvFile $csv,
        bool $hasHeader,
        int $lineLimit,
        array $suggestedSchema,
    ): array {
        foreach ($columns as $columnId => $column) {
            $columnValues = [];
            $example = null;

            foreach ($csv->getRecords((int)$columnId) as $line => $recordValue) {
                if ($line > $lineLimit) {
                    break;
                }

                if ($hasHeader && $line === 0) {
                    continue;
                }

                if ($example === null && $recordValue !== '' && $recordValue !== null) {
                    $example = $recordValue;
                }

                $columnValues[] = $recordValue ?? '';
            }

            $base = [];
            if ($hasHeader) {
                $base['name'] = $column;
            }

            if ($example !== null) {
                $base['example'] = $example;
            }

            $suggestedSchema['columns'][$columnId] = \array_merge(
                $base,
                self::analyzeColumn($columnValues, (int)$columnId),
            );
            $suggestedSchema['columns'][$columnId]['rules'] = RuleOptimizer::optimize(
                $suggestedSchema['columns'][$columnId]['rules'],
            );
        }

        return $suggestedSchema;
    }

    /**
     * Analyzes the values of a column and determines the valid rules.
     * @param  array $columnValues the values of the column to analyze
     * @return array the suggested rules for the column
     */
    private static function analyzeColumn(array $columnValues, int $columnId): array
    {
        $validRules = [
            'rules'           => [],
            'aggregate_rules' => [],
        ];

        if (\count($columnValues) === 0) {
            return $validRules;
        }

        return [
            'rules'           => self::analyzeColumnRules($columnValues),
            'aggregate_rules' => self::analyzeColumnAggregateRules($columnValues, $columnId),
        ];
    }

    /**
     * Automatically detects the parameters for parsing a CSV file.
     * @param  null|bool $forceHeader Whether to force the presence of a header row. Null to auto-detect.
     * @param  int       $linesLimit  number of lines to read when detecting parameters
     * @return array     the suggested parameters for parsing the CSV file
     */
    private static function autoDetectCsvParams(string $csvFilename, ?bool $forceHeader, int $linesLimit): array
    {
        $file = new \SplFileObject($csvFilename, 'r');
        $file->setFlags(\SplFileObject::READ_CSV);

        $delimiters = [',', ';', "\t", '|'];
        $delimiterCounts = \array_fill_keys($delimiters, 0);
        $lineCount = 0;
        $headerChecked = false;
        $detectedHeaderFlag = true;

        // Read up to first lines to detect the delimiter
        while (!$file->eof() && $lineCount < $linesLimit) {
            $line = $file->fgets();

            /** @phpstan-ignore-next-line */
            $line = empty($line) ? '' : $line;

            if ($lineCount === 0) {
                foreach ($delimiters as $delimiter) {
                    $delimiterCounts[$delimiter] += \substr_count($line, $delimiter);
                }
            }

            if (!$headerChecked && $lineCount === 0) {
                $detectedHeaderFlag = self::hasHeader($line, self::detectPopularDelimiter($delimiterCounts));
                $headerChecked = true;
            }

            $lineCount++;
        }

        // Choose the delimiter with the maximum count in the first line
        $popularDelimiter = self::detectPopularDelimiter($delimiterCounts);

        // Default quote character and enclosure. We don't detect them for now. We can add this feature later.
        // For now, we use the default values because they are the most common.
        $quoteChar = '\\';
        $enclosure = '"';

        // Check for BOM
        $file->rewind();
        $bom = $file->fread(3) === "\xEF\xBB\xBF";

        // Reset and check encoding
        $file->rewind();
        $data = (string)$file->fread(10240); // Read more bytes for better encoding detection. 10kb should be enough.
        $encoding = \strtolower((string)\mb_detect_encoding($data, 'UTF-8, UTF-16, UTF-32', true));

        return Utils::removeDefaultSettings([
            'header'     => $forceHeader === null ? $detectedHeaderFlag : $forceHeader,
            'delimiter'  => $popularDelimiter,
            'quote_char' => $quoteChar,
            'enclosure'  => $enclosure,
            'encoding'   => $encoding,
            'bom'        => $bom,
        ], (new Schema())->getCsvParams());
    }

    /**
     * Returns the available rule classes for cell and aggregate rules.
     * @return array<string, class-string<\JBZoo\CsvBlueprint\Rules\AbstractRule>>
     */
    private static function getRuleClasses(string $directory, string $mapTo): array
    {
        static $availableRules = []; // Memoization to avoid multiple file system scans

        if (!isset($availableRules[$mapTo])) {
            $finder = (new Finder())
                ->in(__DIR__ . "/../Rules/{$directory}")
                ->ignoreDotFiles(false)
                ->ignoreVCS(true)
                ->name('/\\.php$/')
                ->files();

            foreach ($finder as $file) {
                $filename = $file->getFilenameWithoutExtension();
                $ruleName = Utils::camelToKebabCase($filename);

                /** @var class-string<\JBZoo\CsvBlueprint\Rules\AbstractRule> $ruleClassname */
                $ruleClassname = "JBZoo\\CsvBlueprint\\Rules\\{$directory}\\{$filename}";

                if (\class_exists($ruleClassname)) {
                    try {
                        $reflection = new \ReflectionClass($ruleClassname);
                        $origClassOfMethodOne = $reflection->getMethod('testValue')->class;
                        $origClassOfMethodMulti = $reflection->getMethod('analyzeColumnValues')->class;

                        if ($ruleClassname !== $origClassOfMethodOne && $ruleClassname !== $origClassOfMethodMulti) {
                            continue;
                        }

                        $availableRules[$mapTo][$ruleName] = $ruleClassname;
                    } catch (\ReflectionException) {
                        continue;
                    }
                }
            }
        }

        return $availableRules[$mapTo];
    }

    /**
     * Detects the most popular delimiter from the given delimiter counts.
     * @param  non-empty-array<array-key, int> $delimiterCounts the counts of occurrences for each delimiter
     * @return string                          the most popular delimiter found
     */
    private static function detectPopularDelimiter(array $delimiterCounts): string
    {
        return (string)\array_search(\max($delimiterCounts), $delimiterCounts, true);
    }

    /**
     * Checks if the header row in the CSV file is valid.
     * @param  string $line      the header row line from the CSV file
     * @param  string $delimiter the delimiter used in the CSV file
     * @return bool   true if the header row is valid, false otherwise
     */
    private static function hasHeader(string $line, string $delimiter): bool
    {
        if ($delimiter === '') {
            return false;
        }

        $fields = \explode($delimiter, $line);

        foreach ($fields as $field) {
            if (\preg_match('/^[a-z0-9_]+$/i', \trim($field)) === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Suggest a filename pattern based on the CSV filename.
     * @param  string $csvFilename the CSV filename
     * @return string the suggested filename pattern
     */
    private static function suggestFilenamePattern(string $csvFilename): string
    {
        return '/' . \preg_quote(\basename($csvFilename), '/') . '$/';
    }
}
