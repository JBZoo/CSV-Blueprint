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

use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;
use Symfony\Component\Finder\Finder;

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;

final class ExampleSchemasTest extends TestCase
{
    public function testFullListOfRules(): void
    {
        $rulesInConfig = yml(Tools::SCHEMA_FULL)->findArray('columns.0.rules');
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
        $defaultsInDoc = yml(Tools::SCHEMA_FULL)->findArray('csv');

        $schema = new Schema([]);
        $schema->getCsvStructure()->getArrayCopy();

        isSame($defaultsInDoc, $schema->getCsvStructure()->getArrayCopy());
    }

    public function testCompareExamplesWithOrig(): void
    {
        $basepath = PROJECT_ROOT . '/schema-examples/full';
        $origYml  = yml(Tools::SCHEMA_FULL)->getArrayCopy();

        isSame((string)phpArray(Tools::SCHEMA_FULL_PHP), (string)phpArray($origYml), 'PHP config is invalid');
        isSame((string)json(Tools::SCHEMA_FULL_JSON), (string)json($origYml), 'JSON config is invalid');
    }

    public function testUniqueNameOfRules(): void
    {
        $yml = yml(Tools::SCHEMA_FULL);

        $rules     = \array_keys($yml->findArray('columns.0.rules'));
        $agRules   = \array_keys($yml->findArray('columns.0.aggregate_rules'));
        $notUnique = \array_intersect($rules, $agRules);

        isSame([], $notUnique, 'Rules names should be unique: ' . \implode(', ', $notUnique));
    }

    public function testRuleNaming(): void
    {
        $yml = yml(Tools::SCHEMA_FULL);

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

    public function testCheckYmlSchemaExampleInReadme(): void
    {
        $filepath = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents(Tools::SCHEMA_FULL)), 12),
        );

        $tmpl = \implode("\n", ['```yml', $filepath, '```']);

        isFileContains($tmpl, PROJECT_ROOT . '/README.md');
    }
}
