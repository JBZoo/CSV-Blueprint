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

use JBZoo\CsvBlueprint\Rules\AbstractRule;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\Utils\FS;
use Symfony\Component\Finder\SplFileInfo;

final class UtilsTest extends TestCase
{
    public function testKebabToCamelCase(): void
    {
        isSame('Kebab', Utils::kebabToCamelCase('kebab'));
        isSame('KebabCaseString', Utils::kebabToCamelCase('kebab-case-string'));
        isSame('KebabCaseString', Utils::kebabToCamelCase('kebab_case_string'));
    }

    public function testCamelToKebabCase(): void
    {
        isSame('kebab', Utils::camelToKebabCase('Kebab'));
        isSame('kebab_case_string', Utils::camelToKebabCase('KebabCaseString'));
    }

    public function testPrepareRegex(): void
    {
        isSame(null, Utils::prepareRegex(null));
        isSame(null, Utils::prepareRegex(''));
        isSame('/.*/', Utils::prepareRegex('.*'));
        isSame('#.*#u', Utils::prepareRegex('#.*#u'));
        isSame('/.*/', Utils::prepareRegex('/.*/'));
        isSame('/.*/ius', Utils::prepareRegex('/.*/ius'));
    }

    public function testFindFiles(): void
    {
        isSame(['demo.csv'], $this->getFileName(Utils::findFiles([
            PROJECT_ROOT . '/tests/fixtures/demo.csv',
        ])));

        isSame([], $this->getFileName(Utils::findFiles([])));
        isSame([], $this->getFileName(Utils::findFiles([''])));

        $this->getFileName(Utils::findFiles(['*.qwerty']));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv'], $this->getFileName(Utils::findFiles([
            PROJECT_ROOT . '/tests/fixtures/batch/*.csv',
        ])));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv'], $this->getFileName(Utils::findFiles([
            './tests/fixtures/batch/*.csv',
        ])));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv'], $this->getFileName(Utils::findFiles([
            './tests/fixtures/batch/*.csv',
        ])));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv'], $this->getFileName(Utils::findFiles(['**/demo-*.csv'])));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv', 'demo.csv'], $this->getFileName(Utils::findFiles([
            PROJECT_ROOT . '/tests/fixtures/batch/*.csv',
            PROJECT_ROOT . '/tests/fixtures/demo.csv',
        ])));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv', 'demo.csv'], $this->getFileName(Utils::findFiles([
            PROJECT_ROOT . '/tests/fixtures/demo.csv',
            PROJECT_ROOT . '/tests/fixtures/batch/*.csv',
        ])));

        isSame(
            [
                'demo-1.csv',
                'demo-2.csv',
                'demo-3.csv',
                'big_header.csv',
                'complex_header.csv',
                'complex_no_header.csv',
                'demo.csv',
                'demo_invalid.csv',
                'empty_cells.csv',
                'empty_header.csv',
                'empty_no_header.csv',
                'simple_header.csv',
                'simple_no_header.csv',
            ],
            $this->getFileName(Utils::findFiles(['tests/**/*.csv'])),
        );
    }

    public function testFindFilesNotFound(): void
    {
        isSame([], $this->getFileName(Utils::findFiles(['demo.csv'])));
    }

    public function testPrintList(): void
    {
        isSame('["one", "two", "three"]', Utils::printList(['one', 'two', 'three']));
        isSame('["<c>one</c>", "<c>two</c>", "<c>three</c>"]', Utils::printList(['one', 'two', 'three'], 'c'));
        isSame('"one"', Utils::printList(['one']));
        isSame('"one"', Utils::printList('one'));
        isSame('"<c>one</c>"', Utils::printList(['one'], 'c'));
        isSame('"<c>one</c>"', Utils::printList('one', 'c'));
        isSame('[]', Utils::printList([]));
        isSame('[]', Utils::printList([], 'c'));
    }

    public function testColorsTags(): void
    {
        $packs = [
            FS::ls(PROJECT_ROOT . '/src'),
            FS::ls(PROJECT_ROOT . '/tests'),
            [PROJECT_ROOT . '/README.md'],
        ];

        $tags = \explode(
            '|',
            'i|c|q|e' .
            '|comment|info|error|question' .
            '|black|red|green|yellow|blue|magenta|cyan|white|default' .
            '|bl|b|u|r|bg' .
            '|details|summary',
        );

        foreach ($packs as $files) {
            foreach ($files as $filepath) {
                foreach ($tags as $tag) {
                    $source = \file_get_contents($filepath);
                    $open = \substr_count($source, "<{$tag}>");
                    $close = \substr_count($source, "</{$tag}>");
                    isTrue($open === $close, "Tag: \"{$tag}\"; Open({$open}) != close({$close}) in file: {$filepath}");
                }
            }
        }
    }

    public function testFixCliArguments(): void
    {
        isSame([], Utils::fixArgv([]));

        isSame(['cmd'], Utils::fixArgv(['cmd']));
        isSame(['cmd'], Utils::fixArgv(['cmd', '']));

        isSame(['cmd', '-h'], Utils::fixArgv(['cmd', '', '-h']));
        isSame(['cmd', '-h'], Utils::fixArgv(['cmd', '', '-h']));
        isSame(['cmd', '-h'], Utils::fixArgv(['cmd', '', ' -h ']));
        isSame(['cmd', '"-h"'], Utils::fixArgv(['cmd', '', ' "-h" ']));

        isSame(
            ['cmd', '-h', '--ansi'],
            Utils::fixArgv(['cmd', '', ' -h ', 'options: --ansi']),
        );
        isSame(
            ['cmd', '-h'],
            Utils::fixArgv(['cmd', '', ' -h ', 'options:']),
        );
        isSame(
            ['cmd', '-h'],
            Utils::fixArgv(['cmd', '', ' -h ', ' options: ']),
        );
        isSame(
            ['cmd', '-h', '--ansi', '--no'],
            Utils::fixArgv(['cmd', '', ' -h ', 'options: --ansi --no']),
        );
        isSame(
            ['cmd', '-h', '--ansi', '--no'],
            Utils::fixArgv(['cmd', '', ' -h ', 'options: --ansi   --no  ']),
        );

        // Test legacy "extra:"
        isSame(
            ['cmd', '-h', '--ansi', '--no'],
            Utils::fixArgv(['cmd', '', ' -h ', 'extra: --ansi   --no  ']),
        );
    }

    public function testParseVersion(): void
    {
        // Stable
        isSame(
            '<info>v0.34</info>  29 Mar 2024 18:09 UTC',
            Utils::parseVersion("0.34 | true | master | 2024-03-29T22:09:21+04:00 | 03c14cc\n", true),
        );
        isSame('v0.34', Utils::parseVersion("0.34 | true | master | 2024-03-29T22:09:21+04:00 | 03c14cc\n", false));

        // Development
        isSame(
            '<info>v0.34</info>  29 Mar 2024 18:09 UTC  <comment>Experimental!</comment>  Branch: branch (03c14cc)',
            Utils::parseVersion("0.34 | false | branch | 2024-03-29T22:09:21+04:00 | 03c14cc\n", true),
        );
        isSame('v0.34', Utils::parseVersion("0.34 | false | branch | 2024-03-29T22:09:21+04:00 | 03c14cc\n", false));

        // Night build
        isSame(
            '<info>v0.34</info>  29 Mar 2024 18:09 UTC  <comment>Night build</comment>  Branch: master (03c14cc)',
            Utils::parseVersion("0.34 | false | master | 2024-03-29T22:09:21+04:00 | 03c14cc\n", true),
        );
        isSame('v0.34', Utils::parseVersion("0.34 | false | master | 2024-03-29T22:09:21+04:00 | 03c14cc\n", false));
    }

    public function testColorOfCellValue(): void
    {
        $packs = [
            FS::ls(PROJECT_ROOT . '/src/Rules/Aggregate'),
            FS::ls(PROJECT_ROOT . '/src/Rules/Cell'),
        ];

        $exclude = [
            'Abstract',
            'Exception',
            'Aggregate/Combo',
            'Cell/Combo',
            'Sorted',
            'IsBase64',
            'IsBool',
            'IsCardinalDirection',
            'IsUnique',
            'NotEmpty',
        ];

        foreach ($packs as $files) {
            foreach ($files as $filepath) {
                foreach ($exclude as $excludeItem) {
                    if (\str_contains($filepath, $excludeItem)) {
                        continue 2;
                    }
                }

                $source = \file_get_contents($filepath);
                isTrue(\str_contains($source, '\"<c>'), 'Coloring is not found in file: ' . $filepath);
                isTrue(\str_contains($source, '</c>\"'), 'Coloring is not found in file: ' . $filepath);
            }
        }
    }

    public function testIsArrayInOrder(): void
    {
        isTrue(Utils::isArrayInOrder(['a', 'b', 'c'], ['a', 'b', 'c']));
        isTrue(Utils::isArrayInOrder(['a', 'b'], ['a', 'b', 'c']));
        isTrue(Utils::isArrayInOrder(['b', 'c'], ['a', 'b', 'c']));
        isTrue(Utils::isArrayInOrder(['a', 'c'], ['a', 'b', 'c']));
        isTrue(Utils::isArrayInOrder(['a'], ['a', 'b', 'c']));
        isTrue(Utils::isArrayInOrder(['b'], ['a', 'b', 'c']));
        isTrue(Utils::isArrayInOrder(['c'], ['a', 'b', 'c']));
        isTrue(Utils::isArrayInOrder([], ['a', 'b', 'c']));

        isTrue(Utils::isArrayInOrder(['d'], ['a', 'b', 'c'])); // ignore extra

        isFalse(Utils::isArrayInOrder(['a', 'c', 'b'], ['a', 'b', 'c']));
        isFalse(Utils::isArrayInOrder(['c', 'a', 'b'], ['a', 'b', 'c']));
        isFalse(Utils::isArrayInOrder(['b', 'a'], ['a', 'b', 'c']));
    }

    public function testAnalyzeGuard(): void
    {
        $type = AbstractRule::INPUT_TYPE_COUNTER;
        isSame([], Utils::analyzeGuard([], $type));
        isSame([1], Utils::analyzeGuard([1], $type));
        isSame(['1'], Utils::analyzeGuard(['1'], $type));
        isSame(['1', ''], Utils::analyzeGuard(['1', ''], $type));
        isSame(['1', ' '], Utils::analyzeGuard(['1', ' '], $type));
        isSame(['1', 2, ' '], Utils::analyzeGuard(['1', 2, ' '], $type));
        isSame(['1', 2, ' ', ''], Utils::analyzeGuard(['1', 2, ' ', ''], $type));
        isSame(['1', 2, ' ', '', true], Utils::analyzeGuard(['1', 2, ' ', '', true], $type));
        isSame(['1', 2, ' ', '', true], Utils::analyzeGuard(['1', 2, ' ', '', true], $type));
        isSame(
            ['1', 2, 3.0, ' ', '', true, 'qwerty'],
            Utils::analyzeGuard(['1', 2, 3.0, ' ', '', true, 'qwerty'], $type),
        );

        $type = AbstractRule::INPUT_TYPE_INTS;
        isSame(null, Utils::analyzeGuard([], $type));
        isSame([1], Utils::analyzeGuard([1], $type));
        isSame([1], Utils::analyzeGuard(['1'], $type));
        isSame([1], Utils::analyzeGuard(['1', ''], $type));
        isSame([1], Utils::analyzeGuard(['1', ' '], $type));
        isSame([1, 2], Utils::analyzeGuard(['1', 2, ' '], $type));
        isSame([1, 2], Utils::analyzeGuard(['1', 2, ' ', ''], $type));
        isSame([0 => 1, 1 => 2, 4 => 1], Utils::analyzeGuard(['1', 2, ' ', '', true], $type));
        isSame(null, Utils::analyzeGuard(['1', 2, 3.0, ' ', '', true, 'qwerty'], $type));

        $type = AbstractRule::INPUT_TYPE_FLOATS;
        isSame(null, Utils::analyzeGuard([], $type));
        isSame([1.0], Utils::analyzeGuard([1], $type));
        isSame([1.0], Utils::analyzeGuard(['1'], $type));
        isSame([1.0], Utils::analyzeGuard(['1', ''], $type));
        isSame([1.0], Utils::analyzeGuard(['1', ' '], $type));
        isSame([1.0, 2.0], Utils::analyzeGuard(['1', 2, ' '], $type));
        isSame([1.0, 2.0], Utils::analyzeGuard(['1', 2, ' ', ''], $type));
        isSame([0 => 1.0, 1 => 2.0, 4 => 1.0], Utils::analyzeGuard(['1', 2, ' ', '', true], $type));
        isSame(null, Utils::analyzeGuard(['1', 2, 3.0, ' ', '', true, 'qwerty'], $type));

        $type = AbstractRule::INPUT_TYPE_STRINGS;
        isSame([], Utils::analyzeGuard([], $type));
        isSame(['1'], Utils::analyzeGuard([1], $type));
        isSame(['1'], Utils::analyzeGuard(['1'], $type));
        isSame(['1', ''], Utils::analyzeGuard(['1', ''], $type));
        isSame(['1', ' '], Utils::analyzeGuard(['1', ' '], $type));
        isSame(['1', '2', ' '], Utils::analyzeGuard(['1', 2, ' '], $type));
        isSame(['1', '2', ' ', ''], Utils::analyzeGuard(['1', 2, ' ', ''], $type));
        isSame(['1', '2', ' ', '', '1'], Utils::analyzeGuard(['1', 2, ' ', '', true], $type));
        isSame(
            ['1', '2', '3', ' ', '', '1', 'qwerty'],
            Utils::analyzeGuard(['1', 2, 3.0, ' ', '', true, 'qwerty'], $type),
        );
    }

    /**
     * @param  SplFileInfo[] $files
     * @return string[]
     */
    private function getFileName(array $files): array
    {
        return \array_values(\array_map(static fn (SplFileInfo $file) => $file->getFilename(), $files));
    }
}
