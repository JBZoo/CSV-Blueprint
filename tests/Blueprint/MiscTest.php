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

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;
use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;
use function JBZoo\PHPUnit\isTrue;

final class MiscTest extends PHPUnit
{
    public function testFullListOfRules(): void
    {
        $rulesInConfig = yml(PROJECT_ROOT . '/schema-examples/full.yml')->findArray('columns.0.rules');
        $rulesInConfig = \array_keys($rulesInConfig);
        \sort($rulesInConfig);

        $finder = (new Finder())
            ->files()
            ->in(PROJECT_ROOT . '/src/Rules/Cell')
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->name('/\\.php$/')
            ->sortByName(true);

        foreach ($finder as $file) {
            $ruleName = Utils::camelToKebabCase($file->getFilenameWithoutExtension());

            $excludeRules = [
                'abstarct_cell_rule',
                'exception',
            ];

            if (\in_array($ruleName, $excludeRules, true)) {
                continue;
            }

            if (\str_contains($ruleName, 'combo_')) {
                $ruleName      = \str_replace('combo_', '', $ruleName);
                $rulesInCode[] = $ruleName;
                $rulesInCode[] = "{$ruleName}_not";
                $rulesInCode[] = "{$ruleName}_min";
                $rulesInCode[] = "{$ruleName}_max";
            } else {
                $rulesInCode[] = $ruleName;
            }
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

    public function testUniqueNameOfRules(): void
    {
        $yml = yml(PROJECT_ROOT . '/schema-examples/full.yml');

        $rules     = \array_keys($yml->findArray('columns.0.rules'));
        $agRules   = \array_keys($yml->findArray('columns.0.aggregate_rules'));
        $notUnique = \array_intersect($rules, $agRules);

        isSame([], $notUnique, 'Rules names should be unique: ' . \implode(', ', $notUnique));
    }

    public function testRuleNaming(): void
    {
        $yml = yml(PROJECT_ROOT . '/schema-examples/full.yml');

        $rules = $yml->findArray('columns.0.rules');

        foreach ($rules as $rule => $option) {
            if ($option === true || $option === false) {
                isTrue(
                    \str_starts_with($rule, 'is_') || \str_starts_with($rule, 'not_'),
                    "Rule name should start with 'is_': {$rule}",
                );
            }
        }
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
