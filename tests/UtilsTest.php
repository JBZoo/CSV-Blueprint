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
use Symfony\Component\Finder\Finder;
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

    public function test(): void
    {
        skip('Skip test. Debugging');
        $finder = (new Finder())
            ->in('/Users/smetdenis/Work/billups/csv-validation-rules/etl_scripts/exposure_delivery/rules')
            ->ignoreVCSIgnored(true)
            ->ignoreDotFiles(true)
            ->followLinks()
            ->name('*.yml')
            ->sortByName(true);

        $results  = [];
        $makefile = [];

        foreach ($finder as $file) {
            $filename = $file->getFilenameWithoutExtension();
            if (\str_starts_with($filename, 'TODO')) {
                continue;
            }

            $ymlTmpl = <<<'YML'
                      - name: _placeholder_
                        uses: jbzoo/csv-blueprint@master
                        with:
                          csv: etl_scripts/exposure_delivery/examples/_placeholder_.*.csv
                          schema: etl_scripts/exposure_delivery/rules/_placeholder_.yml
                          report: table
                YML;

            $csvOption = \str_pad(
                \str_replace(
                    '_placeholder_',
                    $filename,
                    "\${BASE_PATH}/examples/_placeholder_.*.csv' ",
                ),
                58,
            );
            $makeTmpl = "\t\${CSV_BLUEPRINT} --csv='{$csvOption} --schema='\${BASE_PATH}/rules/_placeholder_.yml'";

            $results[]  = \str_replace('_placeholder_', $filename, $ymlTmpl);
            $makefile[] = \str_replace('_placeholder_', $filename, $makeTmpl);
        }
        \file_put_contents(PROJECT_ROOT . '/build/yml.yml', \implode("\n\n", $results) . "\n");
        \file_put_contents(PROJECT_ROOT . '/build/Makefile', \implode("\n", $makefile) . "\n");
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
