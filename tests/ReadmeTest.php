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
        $extraRules = 1 + 1; // filename_pattern, schema validation
        $totalRules = $cellRules + $aggRules + $extraRules;

        $badge = static function (string $label, int $count, string $url = ''): string {
            $label = \str_replace(' ', '%20', $label);
            $badge = "![Static Badge](https://img.shields.io/badge/Rules-{$count}-green" .
                "?label={$label}&labelColor=blue&color=gray)";

            if ($url) {
                return "[{$badge}]({$url})";
            }

            return $badge;
        };

        $text = \implode('    ', [
            $badge('Total Number of Rules', $totalRules, 'schema-examples/full.yml'),
            $badge('Cell Rules', $cellRules, 'src/Rules/Cell'),
            $badge('Aggregate Rules', $aggRules, 'src/Rules/Aggregate'),
            $badge('Extra Checks', $extraRules, 'schema-examples/full.yml'),
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
}
