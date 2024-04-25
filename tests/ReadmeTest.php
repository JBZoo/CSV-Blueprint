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
use JBZoo\CsvBlueprint\SchemaDataPrep;
use JBZoo\Utils\Cli;
use JBZoo\Utils\Str;
use Symfony\Component\Console\Input\StringInput;

use function JBZoo\Data\yml;

final class ReadmeTest extends TestCase
{
    private const EXTRA_RULES = [
        '- The `filename_pattern` rule verifies that the file name adheres to the specified regex pattern, ' .
        'ensuring file naming conventions are followed.',
        '- Ensures that the `name` property is defined for each column, applicable only when `csv.header` ' .
        'is set to `true`, to guarantee header integrity.',
        '- The `required` property, when set to `true`, mandates the presence of the specified column in ' .
        "the CSV file, enhancing data completeness.\n  This is only relevant if `csv.header` is true.",
        "- Validates that each row contains the correct number of columns, aligning with the schema's defined " .
        'structure, to prevent data misalignment.',
        '- The `strict_column_order` rule checks for the correct sequential order of columns as defined in ' .
        'the schema, ensuring structural consistency.',
        '- The `allow_extra_columns` rule asserts no additional columns are present in the CSV file beyond ' .
        "those specified in the schema,\n  maintaining strict data fidelity.",
        '  - For `csv.header: true`, it checks if the schema contains any column `name` not found in the ' .
        'CSV file, addressing header discrepancies.',
        '  - For `csv.header: false`, it compares the number of columns in the schema against those in the ' .
        'CSV file, ensuring schema conformity.',
    ];

    public function testValidateCsvHelp(): void
    {
        $text = \implode("\n", [
            '```txt',
            \trim(Tools::realExecution('validate-csv', ['help' => null])),
            '```',
        ]);

        Tools::insertInReadme('validate-csv-help', $text);
    }

    public function testValidateSchemaHelp(): void
    {
        $text = \implode("\n", [
            '```txt',
            \trim(Tools::realExecution('validate-schema', ['help' => null])),
            '```',
        ]);

        Tools::insertInReadme('validate-schema-help', $text);
    }

    public function testCreateSchemaHelp(): void
    {
        $text = \implode("\n", [
            '```txt',
            \trim(Tools::realExecution('create-schema', ['help' => null])),
            '```',
        ]);

        Tools::insertInReadme('create-schema-help', $text);
    }

    public function testDumpSchemaHelp(): void
    {
        $text = \implode("\n", [
            '```txt',
            \trim(Tools::realExecution('debug-schema', ['help' => null])),
            '```',
        ]);

        Tools::insertInReadme('debug-schema-help', $text);
    }

    public function testTableOutputExample(): void
    {
        success('Replaced to image');
        return;
        $options = [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
        ];
        $optionsAsString = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', $options);

        isSame(1, $exitCode, $actual);

        $text = \implode("\n", [
            '```txt',
            "./csv-blueprint validate-csv {$optionsAsString}",
            '',
            '',
            \trim($actual),
            '```',
        ]);

        $text = \str_replace('CSV Blueprint: Unknown version (PhpUnit)', 'CSV Blueprint: vX.Y.Z', $text);

        Tools::insertInReadme('output-table', $text);
    }

    public function testBadgeOfRules(): void
    {
        $cellRules = \count(yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.rules')) - 1;
        $aggRules = \count(yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.aggregate_rules')) - 1;
        $extraRules = \count(self::EXTRA_RULES);
        $totalRules = $cellRules + $aggRules + $extraRules;

        $todoYml = yml(Tools::SCHEMA_TODO);
        $planToAdd = \implode('/', [
            \count($todoYml->findArray('columns.0.rules')),
            \count($todoYml->findArray('columns.0.aggregate_rules')),
            \count([
                'csv.auto_detect',
                'csv.end_of_line',
                'csv.null_values',
                'filename_pattern - multiple',
                'column.faker',
                'column.null_values',
                'column.multiple + column.multiple_separator',
            ]) + \count($todoYml->findArray('structural_rules'))
            + \count($todoYml->findArray('complex_rules')),
        ]);

        $badge = static function (string $label, int|string $count, string $url, string $color): string {
            $label = \str_replace(' ', '%20', $label);
            $badge = "![Static Badge](https://img.shields.io/badge/Rules-{$count}-green" .
                "?label={$label}&labelColor={$color}&color=gray)";
            return $url ? "[{$badge}]({$url})" : $badge;
        };

        $text = \implode("\n", [
            $badge('Total number of rules', $totalRules, 'schema-examples/full.yml', 'darkgreen'),
            $badge('Cell rules', $cellRules, 'src/Rules/Cell', 'blue'),
            $badge('Aggregate rules', $aggRules, 'src/Rules/Aggregate', 'blue'),
            $badge('Extra checks', $extraRules, '#extra-checks', 'blue'),
            $badge('Plan to add', $planToAdd, 'tests/schemas/todo.yml', 'gray'),
        ]);

        Tools::insertInReadme('rules-counter', $text);
    }

    public function testContributingBlock(): void
    {
        $file = PROJECT_ROOT . '/CONTRIBUTING.md';
        isFile($file);

        $text = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents($file)), 1),
        );

        Tools::insertInReadme('contributing', $text);
    }

    public function testCheckYmlSchemaExampleInReadme(): void
    {
        $ymlContent = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents(Tools::SCHEMA_FULL_YML)), 12),
        );

        $text = \implode("\n", ['```yml', \trim($ymlContent), '```']);

        Tools::insertInReadme('full-yml', $text);
    }

    public function testCheckSimpleYmlSchemaExampleInReadme(): void
    {
        $ymlContent = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents('./schema-examples/readme_sample.yml')), 12),
        );

        $text = \implode("\n", ['```yml', \trim($ymlContent), '```']);

        Tools::insertInReadme('readme-sample-yml', $text);
    }

    public function testCheckPresetUsersExampleInReadme(): void
    {
        $ymlContent = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents('./schema-examples/preset_users.yml')), 12),
        );

        $text = \implode("\n", ['```yml', \trim($ymlContent), '```']);

        Tools::insertInReadme('preset-users-yml', $text);
    }

    public function testCheckPresetFeaturesExampleInReadme(): void
    {
        $ymlContent = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents('./schema-examples/preset_features.yml')), 12),
        );

        $text = \implode("\n", ['```yml', \trim($ymlContent), '```']);

        Tools::insertInReadme('preset-features-yml', $text);
    }

    public function testCheckPresetRegexInReadme(): void
    {
        $text = '`' . SchemaDataPrep::getAliasRegex() . '`';
        isFileContains($text, PROJECT_ROOT . '/README.md');
    }

    public function testCheckPresetDatabaseExampleInReadme(): void
    {
        $ymlContent = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents('./schema-examples/preset_database.yml')), 12),
        );

        $text = \implode("\n", ['```yml', \trim($ymlContent), '```']);

        Tools::insertInReadme('preset-database-yml', $text);
    }

    public function testCheckPresetUsageExampleInReadme(): void
    {
        $ymlContent = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents('./schema-examples/preset_usage.yml')), 12),
        );

        $text = \implode("\n", ['```yml', \trim($ymlContent), '```']);

        Tools::insertInReadme('preset-usage-yml', $text);
    }

    public function testCheckPresetUsageRealExampleInReadme(): void
    {
        $schema = new Schema('./schema-examples/preset_usage.yml');

        $text = \implode("\n", ['```yml', \trim($schema->dumpAsYamlString()), '```']);
        $text = \str_replace(PROJECT_ROOT, '.', $text);

        Tools::insertInReadme('preset-usage-real-yml', $text);
    }

    public function testAdditionalValidationRules(): void
    {
        $list[] = '';

        $text = \implode("\n", self::EXTRA_RULES);
        Tools::insertInReadme('extra-rules', $text);
    }

    public function testBenchmarkTable(): void
    {
        $nbsp = static fn (string $text): string => \str_replace(' ', '&nbsp;', $text);
        $timeFormat = static fn (float $time): string => \str_pad(
            \number_format($time, 1) . ' sec',
            8,
            ' ',
            \STR_PAD_LEFT,
        );

        $numberOfLines = 2_000_000;

        $columns = [
            'Quickest',
            'Minimum',
            'Realistic',
            'All aggregations',
        ];

        // Based on v0.39  30 Mar 2024 21:36 UTC (Docker - latest)
        // https://github.com/JBZoo/Csv-Blueprint/actions/runs/8493549134/job/23268008303
        $table = [
            'Columns: 1<br>Size: ~8 MB' => [
                [786, 1187, 762, 52],
                [386, 1096, 373, 68],
                [189, 667, 167, 208],
                [184, 96, 63, 272],
            ],
            'Columns: 5<br>Size: 64 MB' => [
                [545, 714, 538, 52],
                [319, 675, 308, 68],
                [174, 486, 154, 208],
                [168, 96, 61, 272],
            ],
            'Columns: 10<br>Size: 220 MB' => [
                [311, 362, 307, 52],
                [221, 354, 215, 68],
                [137, 294, 125, 208],
                [135, 96, 56, 272],
            ],
            'Columns: 20<br>Size: 1.2 GB' => [
                [103, 108, 102, 52],
                [91, 107, 89, 68],
                [72, 101, 69, 208],
                [71, 96, 41, 272],
            ],
        ];

        $output = ['<table>'];
        $output[] = '<tr>';
        $output[] = "   <td align=\"left\"><b>{$nbsp('File / Profile')}</b><br></td>";
        $output[] = '   <td align="left"><b>Metric</b><br></td>';
        foreach ($columns as $column) {
            $output[] = "   <td align=\"left\"><b>{$nbsp($column)}</b></td>";
        }
        $output[] = '</tr>';

        foreach ($table as $rowName => $row) {
            $output[] = '<tr>';
            $output[] = "   <td>{$nbsp($rowName)}<br><br><br></td>";
            $output[] = '   <td>' . \implode('<br>', [
                $nbsp('Cell rules'),
                $nbsp('Agg rules'),
                $nbsp('Cell + Agg'),
                $nbsp('Peak Memory'),
            ]) . '</td>';
            foreach ($row as $values) {
                $testRes = '';
                foreach ($values as $key => $value) {
                    if ($key === 3) {
                        $testRes .= $value . ' MB';
                    } else {
                        $execTime = $timeFormat($numberOfLines / ($value * 1000));
                        $testRes .= $nbsp("{$value}K, {$execTime}<br>");
                    }
                }

                $output[] = "   <td align=\"right\">{$testRes}</td>";
            }
            $output[] = '</tr>';
        }

        $output[] = '</table>';

        Tools::insertInReadme('benchmark-table', \implode("\n", $output));
    }

    public function testGenerateTOC(): void
    {
        $markdown = \file_get_contents(PROJECT_ROOT . '/README.md');

        // Split the content by code block delimiters
        $splitContent = \preg_split('/(```.*?```)/s', $markdown, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);

        $toc = '';
        $currentContent = '';

        foreach ($splitContent as $section) {
            // If the section is a code block, skip it
            if (\preg_match('/^```/', $section)) {
                continue;
            }

            $currentContent .= $section;
            // Match headers outside of code blocks
            \preg_match_all('/^(#{2,6})\s*(.*)$/m', $currentContent, $matches, \PREG_SET_ORDER);

            foreach ($matches as $match) {
                $level = \strlen($match[1]);
                if ($level === 2) {
                    $title = \trim($match[2]);
                    $slug = Str::slug($title);
                    $toc .= \str_repeat('  ', $level - 2) . "- [{$title}](#{$slug})\n";
                }
            }
            // Reset the current content to prevent duplicate entries
            $currentContent = '';
        }

        Tools::insertInReadme('toc', $toc);
    }
}
