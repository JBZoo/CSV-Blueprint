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

use JBZoo\Utils\Cli;
use Symfony\Component\Console\Input\StringInput;

use function JBZoo\Data\yml;

final class ReadmeTest extends TestCase
{
    private const EXTRA_RULES = [
        '* With `filename_pattern` rule, you can check if the file name matches the pattern.',
        '* Property `name` is not defined in a column. If `csv.header: true`.',
        '* Schema contains an unknown column `name` that is not found in the CSV file. If `csv.header: true`',
    ];

    public function testCreateCsvHelp(): void
    {
        $text = \implode("\n", [
            '```',
            './csv-blueprint validate:csv --help',
            '',
            '',
            Tools::realExecution('validate:csv', ['help' => null]),
            '```',
        ]);

        Tools::insertInReadme('validate-csv-help', $text);
    }

    public function testTableOutputExample(): void
    {
        $options = [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        isSame(1, $exitCode, $actual);

        $text = \implode("\n", [
            '```',
            "./csv-blueprint validate:csv {$optionsAsString}",
            '',
            '',
            $actual,
            '```',
        ]);

        Tools::insertInReadme('output-table', $text);
    }

    public function testBadgeOfRules(): void
    {
        $cellRules  = \count(yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.rules'));
        $aggRules   = \count(yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.aggregate_rules'));
        $extraRules = \count(self::EXTRA_RULES);
        $totalRules = $cellRules + $aggRules + $extraRules;

        $todoYml   = yml(Tools::SCHEMA_TODO);
        $planToAdd = \count($todoYml->findArray('columns.0.rules')) +
            (\count($todoYml->findArray('columns.0.aggregate_rules')) * 4)
            + \count([
                'required',
                'null_values',
                'multiple + separator',
                'strict_column_order',
                'other_columns_possible',
                'complex_rules. one example',
                'inherit',
                'rule not found',
            ])
            - \count([
                'first_value',
                'second_value',
                'last_value',
                'sorted',
                'custom_func',
            ]);

        $badge = static function (string $label, int $count, string $url, string $color): string {
            $label = \str_replace(' ', '%20', $label);
            $badge = "![Static Badge](https://img.shields.io/badge/Rules-{$count}-green" .
                "?label={$label}&labelColor={$color}&color=gray)";

            if ($url) {
                return "[{$badge}]({$url})";
            }

            return $badge;
        };

        $text = \implode('    ', [
            $badge('Total Number of Rules', $totalRules, 'schema-examples/full.yml', 'darkgreen'),
            $badge('Cell Value', $cellRules, 'src/Rules/Cell', 'blue'),
            $badge('Aggregate Column', $aggRules, 'src/Rules/Aggregate', 'blue'),
            $badge('Extra Checks', $extraRules, '#extra-checks', 'blue'),
            $badge('Plan to add', $planToAdd, 'tests/schemas/todo.yml', 'gray'),
        ]);

        Tools::insertInReadme('rules-counter', $text);
    }

    public function testCheckYmlSchemaExampleInReadme(): void
    {
        $ymlContent = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents(Tools::SCHEMA_FULL_YML)), 12),
        );

        $text = \implode("\n", ['```yml', $ymlContent, '```']);

        Tools::insertInReadme('full-yml', $text);
    }

    public function testAdditionalValidationRules(): void
    {
        $list   = self::EXTRA_RULES;
        $list[] = '';

        $text = \implode("\n", self::EXTRA_RULES);
        Tools::insertInReadme('extra-rules', "\n{$text}\n");
    }
}
