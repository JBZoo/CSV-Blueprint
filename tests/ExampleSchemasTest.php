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
        $rulesInConfig = yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.rules');
        unset($rulesInConfig['preset']);
        $rulesInConfig = \array_keys($rulesInConfig);
        \sort($rulesInConfig, \SORT_NATURAL);

        $finder = (new Finder())
            ->files()
            ->in(PROJECT_ROOT . '/src/Rules/Cell')
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->name('/\.php$/')
            ->sortByName(true);

        foreach ($finder as $file) {
            $ruleName = Utils::camelToKebabCase($file->getFilenameWithoutExtension());

            if (\str_contains($ruleName, 'abstract') || \str_contains($ruleName, 'exception')) {
                continue;
            }

            if (\str_contains($ruleName, 'combo_')) {
                $ruleName = \str_replace('combo_', '', $ruleName);
                $rulesInCode[] = $ruleName;
                $rulesInCode[] = "{$ruleName}_min";
                $rulesInCode[] = "{$ruleName}_greater";
                $rulesInCode[] = "{$ruleName}_not";
                $rulesInCode[] = "{$ruleName}_less";
                $rulesInCode[] = "{$ruleName}_max";
            } else {
                $rulesInCode[] = $ruleName;
            }
        }
        \sort($rulesInCode, \SORT_NATURAL);

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

    public function testFullListOfAggregateRules(): void
    {
        $rulesInConfig = yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.aggregate_rules');
        unset($rulesInConfig['preset']);
        $rulesInConfig = \array_keys($rulesInConfig);
        \sort($rulesInConfig, \SORT_NATURAL);

        $finder = (new Finder())
            ->files()
            ->in(PROJECT_ROOT . '/src/Rules/Aggregate')
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->name('/\.php$/')
            ->sortByName(true);

        foreach ($finder as $file) {
            $ruleName = Utils::camelToKebabCase($file->getFilenameWithoutExtension());

            if (\str_contains($ruleName, 'abstract') || \str_contains($ruleName, 'exception')) {
                continue;
            }

            if (\str_contains($ruleName, 'combo_')) {
                $ruleName = \str_replace('combo_', '', $ruleName);
                $rulesInCode[] = $ruleName;
                $rulesInCode[] = "{$ruleName}_min";
                $rulesInCode[] = "{$ruleName}_greater";
                $rulesInCode[] = "{$ruleName}_not";
                $rulesInCode[] = "{$ruleName}_less";
                $rulesInCode[] = "{$ruleName}_max";
            } else {
                $rulesInCode[] = $ruleName;
            }
        }
        \sort($rulesInCode, \SORT_NATURAL);

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

    public function testCsvDefaultValues(): void
    {
        $full = yml(Tools::SCHEMA_FULL_YML)->findArray('csv');
        unset($full['preset']);
        isSame($full, (new Schema([]))->getCsvParams());
    }

    public function testStructuralRules(): void
    {
        $full = yml(Tools::SCHEMA_FULL_YML)->findArray('structural_rules');
        unset($full['preset']);
        isSame($full, (new Schema([]))->getStructuralRulesParams());
    }

    public function testCheckPhpExample(): void
    {
        $basepath = PROJECT_ROOT . '/schema-examples/full';
        $origYml = yml(Tools::SCHEMA_FULL_YML)->getArrayCopy();

        isSame((string)phpArray(Tools::SCHEMA_FULL_PHP), (string)phpArray($origYml), 'PHP config');
    }

    public function testCheckYmlCleanExample(): void
    {
        $basepath = PROJECT_ROOT . '/schema-examples/full';
        $origYml = yml(Tools::SCHEMA_FULL_YML)->getArrayCopy();

        isSame((string)yml(Tools::SCHEMA_FULL_YML_CLEAN), (string)yml($origYml), 'Yml (clean) config');
    }

    public function testCheckJsonExample(): void
    {
        $basepath = PROJECT_ROOT . '/schema-examples/full';
        $origYml = yml(Tools::SCHEMA_FULL_YML)->getArrayCopy();

        isSame((string)json(Tools::SCHEMA_FULL_JSON), (string)json($origYml), 'JSON config');
    }

    public function testUniqueNameOfRules(): void
    {
        $yml = yml(Tools::SCHEMA_FULL_YML);

        $rules = $yml->findArray('columns.0.rules');
        unset($rules['preset']);
        $rules = \array_keys($rules);

        $agRules = $yml->findArray('columns.0.aggregate_rules');
        unset($agRules['preset']);
        $agRules = \array_keys($agRules);

        $notUnique = \array_intersect($rules, $agRules);

        isSame([], $notUnique, 'Rules names should be unique: ' . \implode(', ', $notUnique));
    }

    public function testRuleNaming(): void
    {
        $yml = yml(Tools::SCHEMA_FULL_YML);

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
}
