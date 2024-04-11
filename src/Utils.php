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

    public static function debugSpeed(string $messPrefix, int $lines, float $startTimer): void
    {
        if (self::$debugMode) {
            $kiloLines = \round(($lines / (\microtime(true) - $startTimer)) / 1000);
            self::debug("{$messPrefix} <blue>" . \number_format($kiloLines) . 'K</blue> lines/sec');
        }
    }

    public static function kebabToCamelCase(string $input): string
    {
        return \str_replace(' ', '', \ucwords(\str_replace(['-', '_'], ' ', $input)));
    }

    public static function camelToKebabCase(string $input): string
    {
        return \strtolower((string)\preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

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
     * Find files from given paths.
     * @param  string[]      $paths
     * @return SplFileInfo[]
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

    public static function cutPath(string $fullpath): string
    {
        $pwd = (string)\getcwd();

        if (\strlen($pwd) <= 1) {
            return $fullpath;
        }

        return \str_replace($pwd, '.', $fullpath);
    }

    public static function isDocker(): bool
    {
        return \file_exists('/app/csv-blueprint');
    }

    public static function isGithubActions(): bool
    {
        return self::isDocker() && Env::bool('GITHUB_ACTIONS');
    }

    /**
     * Autodetect the width of the terminal.
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

    public static function testRegex(?string $regex, string $subject): bool
    {
        if ($regex === null || $regex === '' || $subject === '') {
            return false;
        }

        try {
            var_dump([$regex, $subject]);
            if (\preg_match($regex, $subject) === 0) {
                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    /**
     * @param SplFileInfo[] $csvFiles
     * @param SplFileInfo[] $schemaFiles
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

    public static function printFile(string $fullpath, string $tag = 'bright-blue'): string
    {
        $relPath = self::cutPath($fullpath);
        $basename = \pathinfo($relPath, \PATHINFO_BASENAME);
        $directory = \str_replace($basename, '', $relPath);
        return "{$directory}<{$tag}>{$basename}</{$tag}>";
    }

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

    public static function isPhpUnit(): bool
    {
        return \defined('PHPUNIT_COMPOSER_INSTALL') || \defined('__PHPUNIT_PHAR__');
    }

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
     * @param array<string, null|array|bool|string>|int[]|string[] ...$configs
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

    public static function setDebugMode(bool $debugMode): void
    {
        self::$debugMode = $debugMode;
    }

    public static function getDebugMode(): bool
    {
        return self::$debugMode;
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
