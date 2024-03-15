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

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\PHPUnit\PHPUnit;

use function JBZoo\Data\json;
use function JBZoo\PHPUnit\isSame;

final class ValidatorTest extends PHPUnit
{
    private const CSV_SIMPLE_HEADER    = './tests/fixtures/simple_header.csv';
    private const CSV_SIMPLE_NO_HEADER = './tests/fixtures/simple_no_header.csv';
    private const CSV_COMPLEX          = './tests/fixtures/complex_header.csv';
    private const CSV_DEMO             = './tests/fixtures/demo.csv';

    private const SCHEMA_SIMPLE_HEADER    = './tests/schemas/simple_header.yml';
    private const SCHEMA_SIMPLE_NO_HEADER = './tests/schemas/simple_no_header.yml';

    private const SCHEMA_SIMPLE_HEADER_PHP  = './tests/schemas/simple_header.php';
    private const SCHEMA_SIMPLE_HEADER_JSON = './tests/schemas/simple_header.json';

    protected function setUp(): void
    {
        \date_default_timezone_set('UTC');
    }

    public function testUndefinedRule(): void
    {
        $this->expectExceptionMessage(
            'Rule "undefined_rule" not found. Expected classes: ' .
            '\JBZoo\CsvBlueprint\Rules\Cell\UndefinedRule OR \JBZoo\CsvBlueprint\Rules\Aggregate\UndefinedRule',
        );
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'undefined_rule', true));
        $csv->validate();
    }

    public function testValidWithHeader(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, self::SCHEMA_SIMPLE_HEADER);
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testValidWithoutHeader(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_NO_HEADER, self::SCHEMA_SIMPLE_NO_HEADER);
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testInvalidSchemaFile(): void
    {
        $this->expectExceptionMessage('Invalid schema data: undefined_file_name.yml');
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, 'undefined_file_name.yml');
    }

    public function testSchemaAsPhpFile(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, self::SCHEMA_SIMPLE_HEADER_PHP);
        isSame(
            '"min" at line 2, column "0:seq". Value "1" is less than "2".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testSchemaAsJsonFile(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, self::SCHEMA_SIMPLE_HEADER_JSON);
        isSame(
            '"min" at line 2, column "0:seq". Value "1" is less than "2".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testCellRule(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'not_empty', true));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('integer', 'not_empty', true));
        isSame(
            '"not_empty" at line 19, column "0:integer". Value is empty.' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testAggregateRule(): void
    {
        $csv = new CsvFile(self::CSV_DEMO, $this->getAggregateRule('Name', 'is_unique', true));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(self::CSV_DEMO, $this->getAggregateRule('City', 'is_unique', true));
        isSame(
            '"ag:is_unique" at line 1, column "0:City". Column has non-unique values. Unique: 9, total: 10.' . "\n",
            \strip_tags((string)$csv->validate()),
        );

        $csv = new CsvFile(self::CSV_DEMO, $this->getAggregateRule('City', 'is_unique', false));
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testCellRuleNoName(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule(null, 'not_empty', true));
        isSame(
            '"csv.header" at line 1, column "0:". ' .
            'Property "name" is not defined in schema: "_custom_array_".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testQuickStop(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('yn', 'is_email', true));
        isSame(1, $csv->validate(true)->count());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('yn', 'is_email', true));
        isSame(100, $csv->validate(false)->count());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('yn', 'is_email', true));
        isSame(100, $csv->validate()->count());
    }

    public function testErrorToArray(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('yn', 'is_email', true));
        isSame([
            'ruleCode'   => 'is_email',
            'message'    => 'Value "<c>N</c>" is not a valid email',
            'columnName' => '0:yn',
            'line'       => 2,
        ], $csv->validate(true)->get(0)->toArray());
    }

    public function testRenderText(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        isSame(
            '"min" at line 2, column "0:seq". Value "1" is less than "3".' . "\n",
            \strip_tags($csv->validate(true)->render(ErrorSuite::REPORT_TEXT)),
        );

        isSame(
            \implode("\n", [
                '"min" at line 2, column "0:seq". Value "1" is less than "3".',
                '"min" at line 3, column "0:seq". Value "2" is less than "3".' . "\n",
            ]),
            \strip_tags($csv->validate()->render(ErrorSuite::REPORT_TEXT)),
        );
    }

    public function testRenderTable(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        isSame(
            \implode("\n", [
                '+------+---------- simple_header.csv ------------------+',
                '| Line | id:Column | Rule | Message                    |',
                '+------+-----------+------+----------------------------+',
                '| 2    | 0:seq     | min  | Value "1" is less than "3" |',
                '+------+---------- simple_header.csv ------------------+',
                '',
            ]),
            $csv->validate(true)->render(ErrorSuite::RENDER_TABLE),
        );

        isSame(
            \implode("\n", [
                '+------+---------- simple_header.csv ------------------+',
                '| Line | id:Column | Rule | Message                    |',
                '+------+-----------+------+----------------------------+',
                '| 2    | 0:seq     | min  | Value "1" is less than "3" |',
                '| 3    | 0:seq     | min  | Value "2" is less than "3" |',
                '+------+---------- simple_header.csv ------------------+',
                '',
            ]),
            $csv->validate()->render(ErrorSuite::RENDER_TABLE),
        );
    }

    public function testRenderTeamCity(): void
    {
        $csv  = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        $out  = $csv->validate()->render(ErrorSuite::REPORT_TEAMCITY);
        $path = self::CSV_SIMPLE_HEADER;

        $expected = <<<'TEAMCITY'

            ##teamcity[testCount count='2' flowId='42']
            
            ##teamcity[testSuiteStarted name='simple_header.csv' flowId='42']
            
            ##teamcity[testStarted name='min at column 0:seq' locationHint='php_qn://./tests/fixtures/simple_header.csv' flowId='42']
            "min" at line 2, column "0:seq". Value "1" is less than "3".
            ##teamcity[testFinished name='min at column 0:seq' flowId='42']
            
            ##teamcity[testStarted name='min at column 0:seq' locationHint='php_qn://./tests/fixtures/simple_header.csv' flowId='42']
            "min" at line 3, column "0:seq". Value "2" is less than "3".
            ##teamcity[testFinished name='min at column 0:seq' flowId='42']
            
            ##teamcity[testSuiteFinished name='simple_header.csv' flowId='42']
            
            TEAMCITY;

        isSame($expected, $out);
    }

    public function testRenderGithub(): void
    {
        $path = self::CSV_SIMPLE_HEADER;
        $csv  = new CsvFile($path, $this->getRule('seq', 'min', 3));
        isSame(
            \implode("\n", [
                "::error file={$path},line=2::min at column 0:seq%0A\"min\" at line 2, " .
                'column "0:seq". Value "1" is less than "3".',
                '',
                "::error file={$path},line=3::min at column 0:seq%0A\"min\" at line 3, " .
                'column "0:seq". Value "2" is less than "3".',
                '',
            ]),
            $csv->validate()->render(ErrorSuite::REPORT_GITHUB),
        );

        $path = self::CSV_DEMO;
        $csv  = new CsvFile($path, $this->getAggregateRule('City', 'is_unique', true));
        isSame(
            \implode("\n", [
                '::error file=./tests/fixtures/demo.csv,line=1::ag:is_unique at column 0:City%0A"ag:is_unique" ' .
                'at line 1, column "0:City". Column has non-unique values. Unique: 9, total: 10.',
                '',
            ]),
            $csv->validate()->render(ErrorSuite::REPORT_GITHUB),
        );
    }

    public function testRenderGitlab(): void
    {
        $csv  = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        $path = self::CSV_SIMPLE_HEADER;

        $cleanJson = json($csv->validate()->render(ErrorSuite::REPORT_GITLAB))->getArrayCopy();
        unset($cleanJson[0]['fingerprint'], $cleanJson[1]['fingerprint']);

        isSame(
            [
                [
                    'description' => "min at column 0:seq\n\"min\" at line 2, " .
                        'column "0:seq". Value "1" is less than "3".',
                    'severity' => 'major',
                    'location' => ['path' => $path, 'lines' => ['begin' => 2]],
                ],
                [
                    'description' => "min at column 0:seq\n\"min\" at line 3, " .
                        'column "0:seq". Value "2" is less than "3".',
                    'severity' => 'major',
                    'location' => ['path' => $path, 'lines' => ['begin' => 3]],
                ],
            ],
            $cleanJson,
        );
    }

    public function testRenderJUnit(): void
    {
        $csv  = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        $path = self::CSV_SIMPLE_HEADER;
        isSame(
            \implode("\n", [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<testsuites>',
                '  <testsuite name="simple_header.csv" tests="2">',
                "    <testcase name=\"min at column 0:seq\" file=\"{$path}\" line=\"2\">",
                '      <system-out>"min" at line 2, column "0:seq". Value "1" is less than "3".</system-out>',
                '    </testcase>',
                "    <testcase name=\"min at column 0:seq\" file=\"{$path}\" line=\"3\">",
                '      <system-out>"min" at line 3, column "0:seq". Value "2" is less than "3".</system-out>',
                '    </testcase>',
                '  </testsuite>',
                '</testsuites>',
                '',
            ]),
            $csv->validate()->render(ErrorSuite::REPORT_JUNIT),
        );
    }

    public function testGetAvaiableRenderFormats(): void
    {
        isSame([
            'text',
            'table',
            'github',
            'gitlab',
            'teamcity',
            'junit',
        ], ErrorSuite::getAvaiableRenderFormats());
    }

    public function testFilenamePattern(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, ['filename_pattern' => '/demo(-\\d+)?\\.csv$/']);
        isSame(
            '"filename_pattern" at line 1, column "". ' .
            'Filename "./tests/fixtures/complex_header.csv" does not match pattern: "/demo(-\d+)?\.csv$/".',
            \strip_tags((string)$csv->validate()->get(0)),
        );

        $csv = new CsvFile(self::CSV_COMPLEX, ['filename_pattern' => '']);
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, ['filename_pattern' => null]);
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, ['filename_pattern' => '/.*\.csv$/']);
        isSame('', (string)$csv->validate());
    }

    private function getRule(?string $columnName, ?string $ruleName, array|bool|float|int|string $options): array
    {
        return ['columns' => [['name' => $columnName, 'rules' => [$ruleName => $options]]]];
    }

    private function getAggregateRule(
        ?string $columnName,
        ?string $ruleName,
        array|bool|float|int|string $options,
    ): array {
        return ['columns' => [['name' => $columnName, 'aggregate_rules' => [$ruleName => $options]]]];
    }
}
