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

namespace JBZoo\PHPUnit\Blueprint;

use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\PHPUnit\PHPUnit;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;
use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;

final class MiscTest extends PHPUnit
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

    public function testFullListOfRules(): void
    {
        $rulesInConfig = yml(PROJECT_ROOT . '/schema-examples/full.yml')->findArray('columns.0.rules');
        $rulesInConfig = \array_keys($rulesInConfig);
        \sort($rulesInConfig);

        $finder = (new Finder())
            ->files()
            ->in(PROJECT_ROOT . '/src/CellRules')
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->name('/\\.php$/');

        foreach ($finder as $file) {
            $ruleName     = Utils::camelToKebabCase($file->getFilenameWithoutExtension());
            $excludeRules = [
                'abstarct_cell_rule',
                'exception',
            ];

            if (\in_array($ruleName, $excludeRules, true)) {
                continue;
            }

            $rulesInCode[] = $ruleName;
        }
        \sort($rulesInCode);

        isSame(
            $rulesInCode,
            $rulesInConfig,
            "New: \n" . \array_reduce(
                \array_diff($rulesInConfig, $rulesInCode),
                static fn (string $carry, string $item) => $carry . "{$item}: NEW\n",
                '',
            ),
        );

        isSame(
            $rulesInCode,
            $rulesInConfig,
            "Not exists: \n" . \array_reduce(
                \array_diff($rulesInCode, $rulesInConfig),
                static fn (string $carry, string $item) => $carry . "{$item}: FIXME\n",
                '',
            ),
        );
    }

    public function testCsvStrutureDefaultValues(): void
    {
        $defaultsInDoc = yml(PROJECT_ROOT . '/schema-examples/full.yml')->findArray('csv');

        $schema = new Schema([]);
        $schema->getCsvStructure()->getArrayCopy();

        isSame($defaultsInDoc, $schema->getCsvStructure()->getArrayCopy());
    }

    public function testCheckYmlSchemaExampleInReadme(): void
    {
        $this->testCheckExampleInReadme(
            PROJECT_ROOT . '/schema-examples/full.yml',
            'yml',
            'YAML format (with comment)',
            12,
        );
    }

    public function testCompareExamplesWithOrig(): void
    {
        $basepath = PROJECT_ROOT . '/schema-examples/full';
        $origYml  = yml("{$basepath}.yml")->getArrayCopy();

        isSame((string)phpArray($origYml), (string)phpArray("{$basepath}.php"), 'PHP config is invalid');
        isSame((string)json($origYml), (string)json("{$basepath}.json"), 'JSON config is invalid');
    }

    public function testFindFiles(): void
    {
        isSame(['demo.csv'], $this->getFileName(Utils::findFiles([
            PROJECT_ROOT . '/tests/fixtures/demo.csv',
        ])));

        isSame([], $this->getFileName(Utils::findFiles([])));

        $this->getFileName(Utils::findFiles(['*.qwerty']));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv'], $this->getFileName(Utils::findFiles([
            PROJECT_ROOT . '/tests/fixtures/batch/*.csv',
        ])));

        isSame(['demo-1.csv', 'demo-2.csv', 'demo-3.csv'], $this->getFileName(Utils::findFiles([
            'tests/fixtures/batch/*.csv',
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
        $this->expectExceptionMessage('File not found: demo.csv');
        $this->getFileName(Utils::findFiles(['demo.csv']));
    }

    public function testUniqueNameOfRules(): void
    {
        $yml = yml(PROJECT_ROOT . '/schema-examples/full.yml');

        $rules     = \array_keys($yml->findArray('columns.0.rules'));
        $agRules   = \array_keys($yml->findArray('columns.0.aggregate_rules'));
        $notUnique = \array_intersect($rules, $agRules);

        isSame([], $notUnique, 'Rules names should be unique: ' . \implode(', ', $notUnique));
    }

    /**
     * @param  SplFileInfo[] $files
     * @return string[]
     */
    private function getFileName(array $files): array
    {
        return \array_values(\array_map(static fn (SplFileInfo $file) => $file->getFilename(), $files));
    }

    private function testCheckExampleInReadme(
        string $filepath,
        string $type,
        string $title,
        int $skipFirstLines = 0,
    ): void {
        $filepath = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents($filepath)), $skipFirstLines),
        );

        if ($type === 'php') {
            $tmpl = \implode("\n", ['```php', '<?php', $filepath, '```']);
        } else {
            $tmpl = \implode("\n", ["```{$type}", $filepath, '```']);
        }

        if ($type !== 'yml') {
            $tmpl = $this->getSpoiler("Click to see: {$title}", $tmpl);
        }

        isFileContains($tmpl, PROJECT_ROOT . '/README.md');
    }

    private function getSpoiler(string $title, string $body): string
    {
        return \implode("\n", [
            '<details>',
            "  <summary>{$title}</summary>",
            '',
            "{$body}",
            '',
            '</details>',
            '',
        ]);
    }
}
