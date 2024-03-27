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
                'complex_header.csv',
                'complex_no_header.csv',
                'demo.csv',
                'demo_invalid.csv',
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
            '|i|c|q|e' .
            '|comment|info|error|question' .
            '|black|red|green|yellow|blue|magenta|cyan|white|default' .
            '|bl|b|u|r|bg',
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

    public function testColorOfCellValue(): void
    {
        $packs = [
            FS::ls(PROJECT_ROOT . '/src/Rules/Aggregate'),
            FS::ls(PROJECT_ROOT . '/src/Rules/Cell'),
        ];

        $exclude = [
            'Abstract',
            'Aggregate/Combo',
            'Cell/Combo',
            'IsSorted',
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

    /**
     * @param  SplFileInfo[] $files
     * @return string[]
     */
    private function getFileName(array $files): array
    {
        return \array_values(\array_map(static fn (SplFileInfo $file) => $file->getFilename(), $files));
    }
}
