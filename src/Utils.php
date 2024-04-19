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

namespace JBZoo\CsvBlueprint;

use JBZoo\CsvBlueprint\Validators\ValidatorSchema;
use JBZoo\CsvBlueprint\Workers\WorkerPool;
use JBZoo\Utils\Cli;
use JBZoo\Utils\Env;
use JBZoo\Utils\FS;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function JBZoo\Cli\cli;
use function JBZoo\Utils\bool;

final class Utils
{
    public const MAX_DIRECTORY_DEPTH = 10;

    private static bool $debugMode = false;

    /**
     * Checks if the elements in an array are in a specific order.
     * @param  array $array        the array to check the order of
     * @param  array $correctOrder the correct order that the elements should be in
     * @return bool  returns true if the elements are in the correct order, false otherwise
     */
    public static function isArrayInOrder(array $array, array $correctOrder): bool
    {
        $orderIndex = 0;

        foreach ($array as $element) {
            $foundIndex = \array_search($element, \array_slice($correctOrder, $orderIndex), true);
            if ($foundIndex !== false) {
                $orderIndex += (int)$foundIndex + 1;
            } elseif (\in_array($element, $correctOrder, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prints a list of items for CLI output.
     * @param  null|array|bool|float|int|string $items the list of items to print
     * @param  string                           $color the color to apply to each item
     * @return string                           the formatted string representation of the list
     */
    public static function printList(null|array|bool|float|int|string $items, string $color = ''): string
    {
        if (!\is_array($items)) {
            $items = [$items];
        }

        if (\count($items) === 0) {
            return '[]';
        }

        if (\count($items) === 1) {
            $val = \reset($items);
            if ($color === '') {
                return "\"{$val}\"";
            }
            return "\"<{$color}>{$val}</{$color}>\"";
        }

        if ($color === '') {
            return '["' . \implode('", "', $items) . '"]';
        }

        return "[\"<{$color}>" . \implode("</{$color}>\", \"<{$color}>", $items) . "</{$color}>\"]";
    }

    /**
     * Logs a debug message. It works only if debug mode is enabled (--debug).
     * @param string $message the debug message to log
     */
    public static function debug(string $message): void
    {
        if (self::$debugMode) {
            try {
                cli($message);
            } catch (\Throwable) {
                Cli::out(\strip_tags($message));
            }
        }
    }

    /**
     * Debugs the speed of execution. It works only if debug mode is enabled (--debug).
     * @param string $messPrefix the message prefix to display in the debug output
     * @param int    $lines      the number of lines processed
     * @param float  $startTimer the start time of the execution
     */
    public static function debugSpeed(string $messPrefix, int $lines, float $startTimer): void
    {
        if (self::$debugMode) {
            $kiloLines = \round(($lines / (\microtime(true) - $startTimer)) / 1000);
            self::debug("{$messPrefix} <blue>" . \number_format($kiloLines) . 'K</blue> lines/sec');
        }
    }

    /**
     * Convert a kebab-case string to camelCase.
     * @param  string $input the kebab-case string to be converted
     * @return string the converted camelCase string
     */
    public static function kebabToCamelCase(string $input): string
    {
        return \str_replace(' ', '', \ucwords(\str_replace(['-', '_'], ' ', $input)));
    }

    /**
     * Converts a camelCase string to kebab-case.
     *
     * @param  string $input the camelCase string to be converted
     * @return string the converted kebab-case string
     */
    public static function camelToKebabCase(string $input): string
    {
        return \strtolower((string)\preg_replace('/(?<!^)[A-Z]/', '_$0', $input)); // NOSONAR
    }

    /**
     * Prepares a regular expression pattern by adding a delimiter if necessary.
     * @param  null|string $pattern      The regular expression pattern to prepare. If null or empty, returns null.
     * @param  string      $addDelimiter the delimiter to be added if the pattern doesn't already have a delimiter
     * @return null|string the prepared regular expression pattern with a delimiter, or null if the input pattern
     *                     is null or empty
     */
    public static function prepareRegex(?string $pattern, string $addDelimiter = '/'): ?string
    {
        if ($pattern === null || $pattern === '') {
            return null;
        }

        $delimiters = ['/', '#', '!', '@', '~', '`', '%', '|', $addDelimiter];

        foreach ($delimiters as $delimiter) {
            if (\str_starts_with($pattern, $delimiter)) {
                return $pattern;
            }
        }

        return $addDelimiter . $pattern . $addDelimiter;
    }

    /**
     * Find files based on the given paths.
     * @param  string[]      $paths an array of file or directory paths
     * @return SplFileInfo[] an array of SplFileInfo objects representing the found files
     * @throws Exception     if a file is not readable
     */
    public static function findFiles(array $paths): array
    {
        $fileList = [];

        foreach ($paths as $path) {
            $path = \trim($path);
            if ($path === '') {
                continue;
            }

            if (\strpos($path, '*') !== false) {
                $finder = (new Finder())
                    ->in(\dirname($path))
                    ->depth('< ' . self::MAX_DIRECTORY_DEPTH)
                    ->ignoreVCSIgnored(true)
                    ->ignoreDotFiles(true)
                    ->followLinks()
                    ->name(\basename($path));

                foreach ($finder as $file) {
                    if (!$file->isReadable()) {
                        throw new Exception("File is not readable: {$file->getPathname()}");
                    }

                    $fileList[$file->getPathname()] = $file;
                }
            } elseif (\file_exists($path)) {
                $fileList[$path] = new SplFileInfo($path, '', $path);
            }
        }

        \ksort($fileList, \SORT_NATURAL);

        return $fileList;
    }

    /**
     * Cuts the path of a given file and replaces the current working directory with a dot (.).
     * @param  null|string $fullpath the full path of the file
     * @return string      The modified path with the current working directory replaced by a dot (.) or an empty
     *                     string if the $fullpath is null.
     */
    public static function cutPath(?string $fullpath): string
    {
        if ($fullpath === null) {
            return '';
        }

        $pwd = (string)\getcwd();

        if (\strlen($pwd) <= 1) {
            return $fullpath;
        }

        return \str_replace($pwd, '.', $fullpath);
    }

    /**
     * Check if the application is running in a Docker environment.
     * @return bool true if the application is running in Docker, false otherwise
     */
    public static function isDocker(): bool
    {
        return \file_exists('/app/csv-blueprint');
    }

    /**
     * Checks if the application is running on the GitHub Actions platform.
     * @return bool returns true if the application is running on GitHub Actions, otherwise false
     */
    public static function isGithubActions(): bool
    {
        return self::isDocker() && Env::bool('GITHUB_ACTIONS');
    }

    /**
     * Autodetect the width of the terminal.
     * @return int the maximum auto-detected terminal width
     */
    public static function autoDetectTerminalWidth(): int
    {
        static $maxAutoDetected; // Execution optimization

        if ($maxAutoDetected === null) {
            if (self::isGithubActions()) {
                $maxAutoDetected = 200; // GitHub Actions has a wide terminal
            } elseif (self::isDocker()) {
                $maxAutoDetected = 160;
            } else {
                // Fallback to 80 if the terminal width cannot be determined.
                $maxAutoDetected = Env::int('COLUMNS', Cli::getNumberOfColumns());
            }
        }

        return $maxAutoDetected;
    }

    /**
     * Compare two arrays and return the differences between them.
     * @param  array  $expectedSchema the expected schema array
     * @param  array  $actualSchema   the actual schema array
     * @param  string $columnId       the column ID
     * @param  string $keyPrefix      the key prefix
     * @param  string $path           the current path
     * @return array  an array containing the differences between the two arrays
     */
    public static function compareArray(
        array $expectedSchema,
        array $actualSchema,
        string $columnId = '',
        string $keyPrefix = '',
        string $path = '',
    ): array {
        $differences = [];

        // Exclude array params for some rules because it's not necessary to compare them.
        // They have random values, and it's hard to predict them.
        $excludeArrayParamsFor = [
            'rules.contains_none',
            'rules.allow_values',
            'rules.not_allow_values',
            'rules.contains_none',
            'rules.contains_one',
            'rules.contains_any',
            'rules.contains_all',
            'rules.ip_v4_range',
        ];

        foreach ($actualSchema as $key => $value) {
            $curPath = $path === '' ? (string)$key : "{$path}.{$key}";

            if (\in_array($curPath, $excludeArrayParamsFor, true)) {
                if (!\is_array($value)) {
                    $differences[$columnId . '/' . $curPath] = [
                        $columnId,
                        'Expected type "<c>array</c>", actual "<green>' . \gettype($value) . '</green>" in ' .
                        ".{$keyPrefix}.{$curPath}",
                    ];
                }
                continue;
            }

            if (!\array_key_exists($key, $expectedSchema)) {
                if (\strlen($keyPrefix) <= 1) {
                    $message = "Unknown key: .{$curPath}";
                } else {
                    $message = "Unknown key: .{$keyPrefix}.{$curPath}";
                }

                $differences[$columnId . '/' . $curPath] = [$columnId, $message];
                continue;
            }

            if (!self::matchTypes($expectedSchema[$key], $value)) {
                $expectedType = \gettype($expectedSchema[$key]);
                $actualType = \gettype($value);

                $differences[$columnId . '/' . $curPath] = [
                    $columnId,
                    "Expected type \"<c>{$expectedType}</c>\", actual \"<green>{$actualType}</green>\" in " .
                    ".{$keyPrefix}.{$curPath}",
                ];
            } elseif (\is_array($value)) {
                $differences += \array_merge(
                    $differences,
                    self::compareArray($expectedSchema[$key], $value, $columnId, $keyPrefix, $curPath),
                );
            }
        }

        return $differences;
    }

    /**
     * Checks whether the expected type matches the actual type and if there is a valid conversion between them.
     * @param  null|array|bool|float|int|string $expected The expected type
     * @param  null|array|bool|float|int|string $actual   The actual type
     * @return bool                             returns true if the expected type matches the actual type or if there
     *                                          is a valid conversion between them, otherwise returns false
     */
    public static function matchTypes(
        null|array|bool|float|int|string $expected,
        null|array|bool|float|int|string $actual,
    ): bool {
        $expectedType = \gettype($expected);
        $actualType = \gettype($actual);

        $mapOfValidConvertions = [
            'NULL'    => ['string', 'integer', 'double', 'boolean'],
            'array'   => [],
            'boolean' => [],
            'double'  => ['NULL', 'string', 'integer'],
            'integer' => ['NULL'],
            'string'  => ['NULL', 'double', 'integer'],
        ];

        if ($expectedType === $actualType) {
            return true;
        }

        return isset($mapOfValidConvertions[$expectedType])
            && \in_array($actualType, $mapOfValidConvertions[$expectedType], true);
    }

    /**
     * Test if a regex pattern matches a subject string.
     * @param  null|string $regex   the regex pattern to match
     * @param  string      $subject the subject string
     * @return bool        returns true if the pattern does not match the subject, false otherwise
     * @throws Exception   if an invalid regex pattern is provided
     */
    public static function testRegex(?string $regex, string $subject): bool
    {
        if ($regex === null || $regex === '' || $subject === '') {
            return false;
        }

        try {
            return \preg_match($regex, $subject) === 0;
        } catch (\Throwable $exception) {
            throw new Exception("Invalid regex: \"{$regex}\". Error: \"{$exception->getMessage()}\"");
        }
    }

    /**
     * Matches schema files with CSV files based on the filename pattern in the schema.
     * @param  SplFileInfo[] $csvFiles         an array of CSV files to match
     * @param  SplFileInfo[] $schemaFiles      an array of schema files to match
     * @param  bool          $useGlobalSchemas whether to include global schemas without a filename pattern
     * @return array         an array containing the matched pairs of schema and CSV files, as well as additional
     *                       information
     */
    public static function matchSchemaAndCsvFiles(
        array $csvFiles,
        array $schemaFiles,
        bool $useGlobalSchemas = true,
    ): array {
        $csvs = self::makeFileMap($csvFiles);
        $schemas = self::makeFileMap($schemaFiles);
        $result = [
            'found_pairs'    => [],
            'count_pairs'    => 0,
            'global_schemas' => [], // there is no filename_pattern in schema.
        ];

        foreach (\array_keys($schemas) as $schema) {
            $schema = (string)$schema;

            $filePattern = (new Schema($schema))->getFilenamePattern();
            if ($filePattern === null || $filePattern === '') {
                if ($useGlobalSchemas) {
                    $result['global_schemas'][] = $schema;
                } else {
                    continue;
                }
            }

            foreach (\array_keys($csvs) as $csv) {
                $csv = (string)$csv;

                if (!self::testRegex($filePattern, $csv)) {
                    if (!isset($result['found_pairs'][$schema])) {
                        $result['found_pairs'][$schema] = [];
                    }
                    $result['found_pairs'][$schema][] = $csv;
                    $result['count_pairs']++;

                    // Mark as used
                    $schemas[$schema] = true;
                    $csvs[$csv] = true;
                }
            }
        }

        $result['csv_without_schema'] = self::filterNotUsedFiles($csvs);
        $result['schema_without_csv'] = self::filterNotUsedFiles($schemas);

        return $result;
    }

    /**
     * Prints the file path with an optional HTML tag around the filename.
     * @param  string $fullpath the full path of the file
     * @param  string $tag      the HTML tag to wrap the filename with
     * @return string the file path with the filename wrapped in the specified HTML tag
     */
    public static function printFile(string $fullpath, string $tag = 'bright-blue'): string
    {
        $relPath = self::cutPath($fullpath);
        $basename = \pathinfo($relPath, \PATHINFO_BASENAME);
        $directory = \str_replace($basename, '', $relPath);
        return "{$directory}<{$tag}>{$basename}</{$tag}>";
    }

    /**
     * Retrieves the version of the software.
     * @param  bool   $showFull Whether to display the full version information or not. Default is false.
     * @return string the version of the software as a string, or an error message if the version file is
     *                not found
     */
    public static function getVersion(bool $showFull): string
    {
        if (self::isPhpUnit()) {
            return 'Unknown version (PhpUnit)';
        }

        $versionFile = __DIR__ . '/../.version';
        if (!\file_exists($versionFile)) {
            return 'Version file not found';
        }

        return self::parseVersion((string)\file_get_contents($versionFile), $showFull);
    }

    /**
     * Parses the version information from a content string.
     * @param  string $content  the content string containing the version information
     * @param  bool   $showFull Optional. Whether to show the full version information.
     * @return string the parsed version string
     */
    public static function parseVersion(string $content, bool $showFull): string
    {
        $parts = \array_map('trim', \explode('|', $content));
        $expectedParts = 5;
        if (\count($parts) < $expectedParts) {
            return 'Invalid version file format';
        }

        [$tag, $isStable, $branch, $date, $hash] = $parts;
        $dateStr = self::convertTzToUTC($date)->format('d M Y H:i \U\T\C');
        $tag = 'v' . \ltrim($tag, 'v');

        if (!$showFull) {
            return $tag;
        }

        $version = ["<info>{$tag}</info>", $dateStr];

        if (!bool($isStable)) {
            if ($branch === 'master' || $branch === 'main') {
                $version[] = '<comment>Night build</comment>';
            } else {
                $version[] = '<comment>Experimental!</comment>';
            }
            $version[] = "Branch: {$branch} ({$hash})";
        }

        return \implode('  ', $version);
    }

    /**
     * Returns the size of a file.
     * @param  string $csv the path of the file
     * @return string The size of the file, formatted as a string.
     *                If the file is not found, returns 'file not found'.
     */
    public static function getFileSize(string $csv): string
    {
        if (!\file_exists($csv)) {
            return 'file not found';
        }

        if (self::isPhpUnit()) {
            return '123.34 MB';
        }

        return FS::format((int)\filesize($csv));
    }

    /**
     * Checks if the code is running within the PHPUnit environment.
     * @return bool returns true if the code is running within PHPUnit; otherwise, false
     */
    public static function isPhpUnit(): bool
    {
        return \defined('PHPUNIT_COMPOSER_INSTALL') || \defined('__PHPUNIT_PHAR__');
    }

    /**
     * Fix the command line arguments by extracting flags from the original arguments.
     * @param  array $originalArgs the original command line arguments
     * @return array the fixed command line arguments with extracted flags
     */
    public static function fixArgv(array $originalArgs): array
    {
        $newArgumens = [];

        // Extract flags from the command line arguments `extra: --ansi --profile --debug -vvv`
        foreach ($originalArgs as $argValue) {
            $argValue = \trim($argValue);
            if ($argValue === '') {
                continue;
            }

            if (\str_starts_with($argValue, 'extra:') || \str_starts_with($argValue, 'options:')) {
                $extraArgs = \str_replace(['extra:', 'options:'], '', $argValue);
                $flags = \array_filter(
                    \array_map('trim', \explode(' ', $extraArgs)),
                    static fn ($flag): bool => $flag !== '',
                );

                foreach ($flags as $flag) {
                    $newArgumens[] = $flag;
                }
            } else {
                $newArgumens[] = $argValue;
            }
        }

        return $newArgumens;
    }

    /**
     * Merge multiple arrays of configurations into a single configuration array.
     *
     * @param  array<string, null|array|bool|string>|int[]|string[] ...$configs the arrays of configs to be merged
     * @return array                                                the merged configuration array
     */
    public static function mergeConfigs(array ...$configs): array
    {
        $merged = (array)\array_shift($configs); // Start with the first array

        foreach ($configs as $config) {
            foreach ($config as $key => $value) {
                // If both values are arrays
                if (isset($merged[$key]) && \is_array($merged[$key]) && \is_array($value)) {
                    // Check if arrays are associative (assuming keys are consistent across values for simplicity)
                    $isAssoc = \array_keys($value) !== \range(0, \count($value) - 1);
                    if ($isAssoc) {
                        // Merge associative arrays recursively
                        $merged[$key] = self::mergeConfigs($merged[$key], $value);
                    } else {
                        // Replace non-associative arrays entirely
                        $merged[$key] = $value;
                    }
                } else {
                    // Replace the value entirely
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * Set the debug mode.
     * @param bool $debugMode the new value for the debug mode
     */
    public static function setDebugMode(bool $debugMode): void
    {
        self::$debugMode = $debugMode;
    }

    /**
     * Get the current debug mode.
     * @return bool returns the current debug mode
     */
    public static function isDebugMode(): bool
    {
        return self::$debugMode;
    }

    /**
     * Initialize the application by fixing command line arguments,
     * setting up the WorkerPool autoloader, default timezone, and error handling.
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function init(): void
    {
        // Fix for GitHub actions. See action.yml
        $_SERVER['argv'] = self::fixArgv($_SERVER['argv'] ?? []);
        $_SERVER['argc'] = \count($_SERVER['argv']);

        // Init WorkerPool aotoloader (experimental feature)
        WorkerPool::setBootstrap(
            \file_exists(__DIR__ . '/../docker/preload.php')
                ? __DIR__ . '/../docker/preload.php'
                : __DIR__ . '/../vendor/autoload.php',
        );

        // Set default timezone to compare dates in UTC by default
        \date_default_timezone_set('UTC');

        // Convert all errors to exceptions. Looks like we have critical case, and we need to stop or handle it.
        // We have to do it becase tool uses 3rd-party libraries, and we can't trust them.
        // So, we need to catch all errors and handle them.
        \set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            $severity = match ($severity) {
                \E_ERROR, \E_CORE_ERROR, \E_COMPILE_ERROR, \E_USER_ERROR => 'Error',
                \E_WARNING, \E_CORE_WARNING, \E_COMPILE_WARNING, \E_USER_WARNING => 'Warning',
                \E_NOTICE, \E_USER_NOTICE => 'Notice',
                default => 'Unknown',
            };
            throw new Exception("Unexpected {$severity}: \"{$message}\" in file\n{$file}:{$line}");
        });
    }

    /**
     * Remove default settings from an array of original settings.
     * @param  array $original the original settings array
     * @param  array $defaults the default settings array to compare against
     * @return array the modified settings array with default settings removed
     */
    public static function removeDefaultSettings(array $original, array $defaults): array
    {
        foreach ($original as $key => &$value) {
            // Check if the key exists in defaults and values are the same
            if (\array_key_exists($key, $defaults)) {
                if (\is_array($value) && \is_array($defaults[$key])) {
                    $value = self::removeDefaultSettings($value, $defaults[$key]);
                } elseif ($value === $defaults[$key]) {
                    unset($original[$key]);
                }
            }

            // After processing, check if the value is an empty array and unset it
            if (\is_array($value) && \count($value) === 0) {
                unset($original[$key]);
            }
        }

        // Unsetting the reference to avoid unexpected behavior later
        unset($value);

        return $original;
    }

    /**
     * Sort the rules in the given schema dump based on the original rule order.
     * @param  array $schemaDump the schema dump containing the rules to be sorted
     * @return array the sorted schema dump with rules sorted based on the original order
     */
    public static function sortRules(array $schemaDump): array
    {
        $referenceSchema = ValidatorSchema::getExpected()[1];
        $originalCell = \array_keys($referenceSchema['rules']);
        $originalAgg = \array_keys($referenceSchema['aggregate_rules']);

        foreach ($schemaDump['columns'] as $colId => $column) {
            if (isset($column['rules'])) {
                $schemaDump['columns'][$colId]['rules'] = self::sortByArray(
                    $column['rules'],
                    $originalCell,
                );
            }

            if (isset($column['aggregate_rules'])) {
                $schemaDump['columns'][$colId]['aggregate_rules'] = self::sortByArray(
                    $column['aggregate_rules'],
                    $originalAgg,
                );
            }
        }

        return $schemaDump;
    }

    /**
     * Sorts an array based on a reference order.
     * @param  array $dataArray the array to be sorted
     * @param  array $refOrder  the reference order used for sorting
     * @return array the sorted array
     */
    public static function sortByArray(array $dataArray, array $refOrder): array
    {
        \uksort(
            $dataArray,
            static fn ($arrA, $arrB) => \array_search($arrA, $refOrder, true) <=> \array_search($arrB, $refOrder, true),
        );
        return $dataArray;
    }

    /**
     * Analyze the column values and extract numeric values in a numeric array.
     * @param  array      $columnValues the column values to be analyzed
     * @return null|array The array of numeric values extracted from the column values.
     *                    Returns null if there are not enough numeric values or non-numeric values present in
     *                    the column values.
     */
    public static function analyzeGuard(array $columnValues): ?array
    {
        $valuesNotEmpty = \array_filter($columnValues, static fn (string $value): bool => $value !== '');
        $numericArray = \array_filter($valuesNotEmpty, 'is_numeric');

        if (\count($valuesNotEmpty) !== \count($numericArray) || \count($numericArray) === 0) {
            return null;
        }

        return \array_map(static fn ($value) => (float)$value, $numericArray);
    }

    /**
     * @param SplFileInfo[] $files
     */
    private static function makeFileMap(array $files): array
    {
        $filemap = [];

        foreach ($files as $file) {
            $filemap[$file->getRealPath()] = false;
        }

        return $filemap;
    }

    private static function filterNotUsedFiles(array $files): array
    {
        return \array_keys(\array_filter($files, static fn ($value) => $value === false));
    }

    private static function convertTzToUTC(string $dateWithSourceTZ): \DateTime
    {
        return (new \DateTime($dateWithSourceTZ, new \DateTimeZone('UTC')))
            ->setTimezone(new \DateTimeZone('UTC'));
    }
}
