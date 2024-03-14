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
            } else {
                throw new \RuntimeException("File not found: {$path}");
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
        return Env::bool('GITHUB_ACTIONS');
    }
}
