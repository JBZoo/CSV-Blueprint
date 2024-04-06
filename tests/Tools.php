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

namespace JBZoo\PHPUnit;

use JBZoo\Cli\CliApplication;
use JBZoo\CsvBlueprint\Commands\ValidateCsv;
use JBZoo\CsvBlueprint\Commands\ValidateSchema;
use JBZoo\Utils\Cli;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class Tools
{
    public const CSV_SIMPLE_HEADER = './tests/fixtures/simple_header.csv';
    public const CSV_SIMPLE_NO_HEADER = './tests/fixtures/simple_no_header.csv';
    public const CSV_COMPLEX = './tests/fixtures/complex_header.csv';

    public const SCHEMA_SIMPLE_HEADER = './tests/schemas/simple_header.yml';
    public const SCHEMA_SIMPLE_NO_HEADER = './tests/schemas/simple_no_header.yml';
    public const SCHEMA_SIMPLE_HEADER_PHP = './tests/schemas/simple_header.php';
    public const SCHEMA_SIMPLE_HEADER_JSON = './tests/schemas/simple_header.json';
    public const SCHEMA_EXAMPLE_EMPTY = PROJECT_ROOT . '/tests/schemas/example_empty.yml';

    public const SCHEMA_FULL_YML = PROJECT_ROOT . '/schema-examples/full.yml';
    public const SCHEMA_FULL_YML_CLEAN = './schema-examples/full_clean.yml';
    public const SCHEMA_FULL_JSON = './schema-examples/full.json';
    public const SCHEMA_FULL_PHP = './schema-examples/full.php';
    public const SCHEMA_INVALID = './tests/schemas/invalid_schema.yml';

    public const SCHEMA_TODO = './tests/schemas/todo.yml';

    public const DEMO_YML_VALID = './tests/schemas/demo_valid.yml';
    public const DEMO_YML_INVALID = './tests/schemas/demo_invalid.yml';
    public const DEMO_CSV = './tests/fixtures/demo.csv';
    public const DEMO_INVALID_CSV = './tests/fixtures/demo_invalid.csv';
    public const DEMO_CSV_FULL = PROJECT_ROOT . '/tests/fixtures/demo.csv';

    public const README = PROJECT_ROOT . '/README.md';

    public static function virtualExecution(string $action, array|string $params = []): array
    {
        $application = new CliApplication();
        $application->add(new ValidateCsv());
        $application->add(new ValidateSchema());
        $command = $application->find($action);

        $buffer = new BufferedOutput();
        if (\is_array($params)) {
            $params['no-ansi'] = null;
            $args = new StringInput(Cli::build('', $params));
        } else {
            $args = new StringInput($params);
        }

        $exitCode = $command->run($args, $buffer);

        return [$buffer->fetch(), $exitCode];
    }

    public static function realExecution(string $action, array $params = [], string $extra = '--no-ansi'): string
    {
        $rootDir = PROJECT_ROOT;

        return Cli::exec(
            \implode(' ', [
                'COLUMNS=200',
                Sys::getBinary(),
                "{$rootDir}/csv-blueprint.php {$extra}",
                $action,
                '2>&1',
            ]),
            $params,
            $rootDir,
            false,
        );
    }

    public static function dumpText($text): void
    {
        \file_put_contents(PROJECT_ROOT . '/build/dump.txt', $text);
    }

    public static function getRule(?string $columnName, ?string $ruleName, array|bool|float|int|string $options): array
    {
        return ['columns' => [['name' => $columnName, 'rules' => [$ruleName => $options]]]];
    }

    public static function getAggregateRule(
        ?string $columnName,
        ?string $ruleName,
        array|bool|float|int|string $options,
    ): array {
        return ['columns' => [['name' => $columnName, 'aggregate_rules' => [$ruleName => $options]]]];
    }

    public static function insertInReadme(string $code, string $content, bool $isInline = false): void
    {
        isFile(self::README);
        $prefix = 'auto-update:';
        isFileContains("<!-- {$prefix}{$code} -->", self::README);
        isFileContains("<!-- {$prefix}/{$code} -->", self::README);

        if ($isInline) {
            $replacement = \implode('', [
                "<!-- {$prefix}{$code} -->",
                $content,
                "<!-- {$prefix}/{$code} -->",
            ]);
        } else {
            $replacement = \implode("\n", [
                "<!-- {$prefix}{$code} -->",
                \trim($content),
                "<!-- {$prefix}/{$code} -->",
            ]);
        }

        $result = \preg_replace(
            "/<\\!-- {$prefix}{$code} -->(.*?)<\\!-- {$prefix}\\/{$code} -->/s",
            $replacement,
            \file_get_contents(self::README),
        );

        $hashBefore = \hash_file('md5', self::README);
        \clearstatcache(true, self::README);
        isTrue(\file_put_contents(self::README, $result) > 0);
        $hashAfter = \hash_file('md5', self::README);

        isSame(
            $hashAfter,
            $hashBefore,
            "README.md was not updated. Code: {$code}\n\n---------\n{$replacement}\n---------",
        );
        isFileContains($result, self::README);
    }

    public static function findKeysToRemove(array $current, array $todo, $path = ''): array
    {
        $keysToRemove = [];

        foreach ($todo as $key => $value) {
            $currentPath = $path === '' ? $key : $path . '.' . $key;

            if (\array_key_exists($key, $current)) {
                if (\is_array($value) && \is_array($current[$key])) {
                    $keysToRemove = \array_merge(
                        $keysToRemove,
                        self::findKeysToRemove($current[$key], $value, $currentPath),
                    );
                } else {
                    $keysToRemove[] = $currentPath;
                }
            }
        }

        return $keysToRemove;
    }

    public static function arrayToOptionString(array $options): string
    {
        $optionsAsArray = [];

        foreach ($options as $key => $subOptions) {
            if (\is_array($subOptions)) {
                foreach ($subOptions as $option) {
                    $optionsAsArray[] = "--{$key}=\"{$option}\"";
                }
            } else {
                $optionsAsArray[] = "--{$key}=\"{$subOptions}\"";
            }
        }

        return \implode(' ', $optionsAsArray);
    }

    public static function range(int $min = 1, int $max = 10): array
    {
        return \array_map('strval', \range($min, $max));
    }
}
