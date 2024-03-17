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

namespace JBZoo\PHPUnit\Validators;

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

final class CsvValidatorTest extends TestCase
{
    public function testUndefinedRule(): void
    {
        $this->expectExceptionMessage('Rule "undefined_rule" not found.');
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('seq', 'undefined_rule', true));
        $csv->validate();
    }

    public function testValidWithHeader(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::SCHEMA_SIMPLE_HEADER);
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testValidWithoutHeader(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_NO_HEADER, Tools::SCHEMA_SIMPLE_NO_HEADER);
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testInvalidSchemaFile(): void
    {
        $this->expectExceptionMessage('Invalid schema data: undefined_file_name.yml');
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, 'undefined_file_name.yml');
    }

    public function testSchemaAsPhpFile(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::SCHEMA_SIMPLE_HEADER_PHP);
        isSame(
            '"num_min" at line 2, column "0:seq". ' .
            'The number of the value "1", which is less than the expected "2".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testSchemaAsJsonFile(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::SCHEMA_SIMPLE_HEADER_JSON);
        isSame(
            '"num_min" at line 2, column "0:seq". ' .
            'The number of the value "1", which is less than the expected "2".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testCellRule(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('seq', 'not_empty', true));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('integer', 'not_empty', true));
        isSame(
            '"not_empty" at line 19, column "0:integer". Value is empty.' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testAggregateRule(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('Name', 'is_unique', true));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('City', 'is_unique', true));
        isSame(
            '"ag:is_unique" at line 1, column "0:City". Column has non-unique values. Unique: 9, total: 10.' . "\n",
            \strip_tags((string)$csv->validate()),
        );

        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('City', 'is_unique', false));
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testAggregateRuleCombo(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('Float', 'sum', 4691.3235));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('Float', 'sum', 20));
        isSame(
            '"ag:sum" at line <red>1</red>, column "0:Float". The sum of numbers in the column is ' .
            '"<c>4691.3235</c>", which is not equal than the expected "<green>20</green>".' . "\n",
            (string)$csv->validate(),
        );
    }

    public function testCellRuleNoName(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule(null, 'not_empty', true));
        isSame(
            '"csv.header" at line 1, column "0:". ' .
            'Property "name" is not defined in schema: "_custom_array_".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testQuickStop(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame(1, $csv->validate(true)->count());

        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame(100, $csv->validate(false)->count());

        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame(100, $csv->validate()->count());
    }

    public function testErrorToArray(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame([
            'ruleCode'   => 'is_email',
            'message'    => 'Value "<c>N</c>" is not a valid email',
            'columnName' => '0:yn',
            'line'       => 2,
        ], $csv->validate(true)->get(0)->toArray());
    }

    public function testFilenamePattern(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => '/demo(-\\d+)?\\.csv$/']);
        isSame(
            '"filename_pattern" at line 1, column "". ' .
            'Filename "./tests/fixtures/complex_header.csv" does not match pattern: "/demo(-\d+)?\.csv$/".',
            \strip_tags((string)$csv->validate()->get(0)),
        );

        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => '']);
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => null]);
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => '/.*\.csv$/']);
        isSame('', (string)$csv->validate());
    }
}
