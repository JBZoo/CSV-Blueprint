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
        '* Check that each row matches the number of columns.',
        '* If `csv.header: true`. Schema contains an unknown column `name` that is not found in the CSV file.',
        '* If `csv.header: false`. Compare the number of columns in the schema and the CSV file.',
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
        $optionsAsString = new StringInput(Cli::build('', $options));
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

        $text = \str_replace('CSV Blueprint: Unknown version (PhpUnit)', 'CSV Blueprint: vX.Y.Z', $text);

        Tools::insertInReadme('output-table', $text);
    }

    public function testBadgeOfRules(): void
    {
        $cellRules = \count(yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.rules'))
            + (\count(\hash_algos()) - 1); // Without itself

        $aggRules = \count(yml(Tools::SCHEMA_FULL_YML)->findArray('columns.0.aggregate_rules'));
        $extraRules = \count(self::EXTRA_RULES);
        $totalRules = $cellRules + $aggRules + $extraRules;

        $todoYml = yml(Tools::SCHEMA_TODO);
        $planToAdd = \count($todoYml->findArray('columns.0.rules')) . '/' .
            (\count($todoYml->findArray('columns.0.aggregate_rules')) * 6) . '/' .
            \count([
                'required',
                'null_values',
                'multiple + separator',
                'strict_column_order',
                'other_columns_possible',
                'complex_rules. one example',
                'inherit',
                'rule not found',
            ]);

        $badge = static function (string $label, int|string $count, string $url, string $color): string {
            $label = \str_replace(' ', '%20', $label);
            $badge = "![Static Badge](https://img.shields.io/badge/Rules-{$count}-green" .
                "?label={$label}&labelColor={$color}&color=gray)";

            if ($url) {
                return "[{$badge}]({$url})";
            }

            return $badge;
        };

        $text = \implode('    ', [
            $badge('Total number of rules', $totalRules, 'schema-examples/full.yml', 'darkgreen'),
            $badge('Cell rules', $cellRules, 'src/Rules/Cell', 'blue'),
            $badge('Aggregate rules', $aggRules, 'src/Rules/Aggregate', 'blue'),
            $badge('Extra checks', $extraRules, '#extra-checks', 'blue'),
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
        $list[] = '';

        $text = \implode("\n", self::EXTRA_RULES);
        Tools::insertInReadme('extra-rules', "\n{$text}\n");
    }

    public function testBenchmarkTable(): void
    {
        $nbsp = static fn (string $text): string => \str_replace(' ', '&nbsp', $text);
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

        $table = [
            'Columns: 1<br>Size: 8.48 MB' => [
                [586, 802, 474, 52],
                [320, 755, 274, 68],
                [171, 532, 142, 208],
                [794, 142, 121, 272],
            ],
            'Columns: 5<br>Size: 64.04 MB' => [
                [443, 559, 375, 52],
                [274, 526, 239, 68],
                [156, 406, 131, 208],
                [553, 139, 111, 272],
            ],
            'Columns: 10<br>Size: 220.02 MB' => [
                [276, 314, 247, 52],
                [197, 308, 178, 68],
                [129, 262, 111, 208],
                [311, 142, 97, 272],
            ],
            'Columns: 20<br>Size: 1.18 GB' => [
                [102, 106, 95, 52],
                [88, 103, 83, 68],
                [70, 97, 65, 208],
                [105, 144, 61, 272],
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
                $output[] = '   <td align="right">';
                foreach ($values as $key => $value) {
                    if ($key === 3) {
                        $testRes = $value . ' MB';
                    } else {
                        $execTime = $timeFormat($numberOfLines / ($value * 1000));
                        $testRes = $nbsp("{$value}K, {$execTime}<br>");
                    }

                    $output[] = $testRes;
                }

                $output[] = '</td>';
            }
            $output[] = '</tr>';
        }

        $output[] = '</table>';

        Tools::insertInReadme('benchmark-table', \implode("\n", $output));
    }
}
