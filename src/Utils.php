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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class Utils
{
    public const MAX_DIRECTORY_DEPTH = 10;

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
                        throw new \RuntimeException("File is not readable: {$file->getPathname()}");
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

        foreach ($actualSchema as $key => $value) {
            $curPath = $path === '' ? (string)$key : "{$path}.{$key}";

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
                $actualType   = \gettype($value);

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
        $actualType   = \gettype($actual);

        $mapOfValidConvertions = [
            'NULL'    => [],
            'array'   => [],
            'boolean' => [],
            'double'  => ['string', 'integer'],
            'integer' => ['string', 'double'],
            'string'  => ['double', 'integer'],
        ];

        if ($expectedType === $actualType) {
            return true;
        }

        return isset($mapOfValidConvertions[$expectedType])
            && \in_array($actualType, $mapOfValidConvertions[$expectedType], true);
    }
}
