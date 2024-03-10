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
use function JBZoo\PHPUnit\isContain;
use function JBZoo\PHPUnit\isSame;

final class ValidatorTest extends PHPUnit
{
    private const CSV_SIMPLE_HEADER    = PROJECT_TESTS . '/fixtures/simple_header.csv';
    private const CSV_SIMPLE_NO_HEADER = PROJECT_TESTS . '/fixtures/simple_no_header.csv';
    private const CSV_COMPLEX          = PROJECT_TESTS . '/fixtures/complex_header.csv';

    private const SCHEMA_SIMPLE_HEADER    = PROJECT_TESTS . '/schemas/simple_header.yml';
    private const SCHEMA_SIMPLE_NO_HEADER = PROJECT_TESTS . '/schemas/simple_no_header.yml';

    private const SCHEMA_SIMPLE_HEADER_PHP  = PROJECT_TESTS . '/schemas/simple_header.php';
    private const SCHEMA_SIMPLE_HEADER_JSON = PROJECT_TESTS . '/schemas/simple_header.json';

    protected function setUp(): void
    {
        \date_default_timezone_set('UTC');
    }

    public function testUndefinedRule(): void
    {
        $this->expectExceptionMessage(
            'Rule "undefined_rule" not found. Expected class: "JBZoo\CsvBlueprint\Validators\Rules\UndefinedRule"',
        );
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'undefined_rule', true));
        $csv->validate();
    }

    public function testValidWithHeader(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, self::SCHEMA_SIMPLE_HEADER);
        isSame('', (string)$csv->validate());
    }

    public function testValidWithoutHeader(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_NO_HEADER, self::SCHEMA_SIMPLE_NO_HEADER);
        isSame('', (string)$csv->validate());
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
            '"min" at line 2, column "0:seq". Value "1" is less than "2".',
            (string)$csv->validate(),
        );
    }

    public function testSchemaAsJsonFile(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, self::SCHEMA_SIMPLE_HEADER_JSON);
        isSame(
            '"min" at line 2, column "0:seq". Value "1" is less than "2".',
            (string)$csv->validate(),
        );
    }

    public function testNotEmptyMessage(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'not_empty', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('integer', 'not_empty', true));
        isSame(
            '"not_empty" at line 19, column "0:integer". Value is empty.',
            (string)$csv->validate(),
        );
    }

    public function testNoName(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule(null, 'not_empty', true));
        isSame(
            '"csv.header" at line 1, column "0:". ' .
            'Property "name" is not defined in schema: "_custom_array_".',
            (string)$csv->validate(),
        );
    }

    public function testMin(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'min', -10));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'min', 10));
        isSame(
            '"min" at line 2, column "0:seq". Value "1" is less than "10".',
            (string)$csv->validate()->get(0),
        );
    }

    public function testMax(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'max', 10000));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'max', 10));
        isSame(
            '"max" at line 12, column "0:seq". Value "11" is greater than "10".',
            (string)$csv->validate()->get(0),
        );
    }

    public function testRegex(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'regex', '.*'));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'regex', '^[a-zA-Z0-9]+$'));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'regex', '[a-z]'));
        isSame(
            '"regex" at line 2, column "0:seq". Value "1" does not match the pattern "/[a-z]/u".',
            (string)$csv->validate()->get(0),
        );

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'regex', '/[a-z]/'));
        isSame(
            '"regex" at line 2, column "0:seq". Value "1" does not match the pattern "/[a-z]/".',
            (string)$csv->validate()->get(0),
        );

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'regex', '/[a-z]/i'));
        isSame(
            '"regex" at line 2, column "0:seq". Value "1" does not match the pattern "/[a-z]/i".',
            (string)$csv->validate()->get(0),
        );
    }

    public function testMinLength(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'min_length', 1));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'min_length', 1000));
        isSame(
            '"min_length" at line 2, column "0:seq". Value "1" (legth: 1) is too short. Min length is 1000.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testMaxLength(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'max_length', 10));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'max_length', 1));
        isSame(
            '"max_length" at line 11, column "0:seq". Value "10" (legth: 2) is too long. Max length is 1.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testOnlyTrimed(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'only_trimed', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('sentence', 'only_trimed', true));
        isSame(
            '"only_trimed" at line 14, column "0:sentence". Value " Urecam" is not trimmed.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testOnlyUppercase(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'only_uppercase', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'only_uppercase', true));
        isSame(
            '"only_uppercase" at line 2, column "0:bool". Value "true" is not uppercase.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testOnlyLowercase(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'only_lowercase', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'only_lowercase', true));
        isSame(
            '"only_lowercase" at line 8, column "0:bool". Value "False" should be in lowercase.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testOnlyCapitalize(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'only_capitalize', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'only_capitalize', true));
        isSame(
            '"only_capitalize" at line 2, column "0:bool". Value "true" should be in capitalize.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testPrecision(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'precision', 0));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'precision', 1));
        isSame(
            '"precision" at line 2, column "0:seq". ' .
            'Value "1" has a precision of 0 but should have a precision of 1.',
            (string)$csv->validate()->get(0),
        );

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('float', 'precision', 3));
        isSame(
            '"precision" at line 3, column "0:float". ' .
            'Value "506847750940.2624" has a precision of 4 but should have a precision of 3.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testMinDate(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'min_date', '2000-01-01'));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'min_date', '2120-01-01'));
        isSame(
            '"min_date" at line 2, column "0:date". ' .
            'Value "2042/11/18" is less than the minimum date "2120-01-01T00:00:00.000+00:00".',
            (string)$csv->validate()->get(0),
        );

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'min_date', '2042/11/17'));
        isSame(
            '"min_date" at line 5, column "0:date". ' .
            'Value "2032/09/09" is less than the minimum date "2042-11-17T00:00:00.000+00:00".',
            (string)$csv->validate()->get(0),
        );
    }

    public function testMaxDate(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'max_date', '2200-01-01'));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'max_date', '2120-01-01'));
        isSame(
            '"max_date" at line 23, column "0:date". ' .
            'Value "2120/02/01" is more than the maximum date "2120-01-01T00:00:00.000+00:00".',
            (string)$csv->validate()->get(0),
        );

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'max_date', '2042/11/17'));
        isSame(
            '"max_date" at line 2, column "0:date". ' .
            'Value "2042/11/18" is more than the maximum date "2042-11-17T00:00:00.000+00:00".',
            (string)$csv->validate()->get(0),
        );
    }

    public function testDateFormat(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'date_format', 'Y/m/d'));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('date', 'date_format', 'Y/m/d H:i:s'));
        isSame(
            '"date_format" at line 2, column "0:date". ' .
            'Date format of value "2042/11/18" is not valid. Expected format: "Y/m/d H:i:s".',
            (string)$csv->validate()->get(0),
        );
    }

    public function testAllowValues(): void
    {
        $csv = new CsvFile(
            self::CSV_COMPLEX,
            $this->getRule(
                'bool',
                'allow_values',
                ['true', 'false', 'False', 'True'],
            ),
        );
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'allow_values', ['true', 'false']));
        isSame(
            '"allow_values" at line 8, column "0:bool". ' .
            'Value "False" is not allowed. Allowed values: ["true", "false"].',
            (string)$csv->validate()->get(0),
        );
    }

    public function testExactValue(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('exact', 'exact_value', '1'));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('exact', 'exact_value', '2'));
        isSame(
            '"exact_value" at line 2, column "0:exact". Value "1" is not strict equal to "2".',
            (string)$csv->validate()->get(0),
        );

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'exact_value', 'true'));
        isSame(
            '"exact_value" at line 4, column "0:bool". Value "false" is not strict equal to "true".',
            (string)$csv->validate()->get(0),
        );
    }

    public function testIsInt(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'is_int', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'is_int', true));
        isSame(
            '"is_int" at line 2, column "0:bool". Value "true" is not an integer.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testIsFloat(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('seq', 'is_float', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'is_float', true));
        isSame(
            '"is_float" at line 2, column "0:bool". Value "true" is not a float number.',
            (string)$csv->validate()->get(0),
        );
    }

    public function testIsBool(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('bool', 'is_bool', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('yn', 'is_bool', true));
        isSame(
            '"is_bool" at line 2, column "0:yn". Value "n" is not allowed. Allowed values: ["true", "false"].',
            (string)$csv->validate()->get(0),
        );
    }

    public function testIsEmail(): void
    {
        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('email', 'is_email', true));
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(self::CSV_COMPLEX, $this->getRule('yn', 'is_email', true));
        isSame(
            '"is_email" at line 2, column "0:yn". Value "N" is not a valid email.',
            (string)$csv->validate()->get(0),
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
            'message'    => 'Value "N" is not a valid email',
            'columnName' => '0:yn',
            'line'       => 2,
        ], $csv->validate(true)->get(0)->toArray());
    }

    public function testRenderText(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        isSame(
            '"min" at line 2, column "0:seq". Value "1" is less than "3".',
            $csv->validate(true)->render(ErrorSuite::RENDER_TEXT),
        );

        isSame(
            \implode("\n", [
                '"min" at line 2, column "0:seq". Value "1" is less than "3".',
                '"min" at line 3, column "0:seq". Value "2" is less than "3".',
            ]),
            $csv->validate()->render(ErrorSuite::RENDER_TEXT),
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
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        $out = $csv->validate()->render(ErrorSuite::RENDER_TEAMCITY);

        isContain("##teamcity[testCount count='2' ", $out);
        isContain("##teamcity[testSuiteStarted name='simple_header.csv' ", $out);
        isContain("##teamcity[testStarted name='min at column 0:seq' locationHint='php_qn://simple_header.csv'", $out);
        isContain("##teamcity[testFinished name='min at column 0:seq' timestamp", $out);
        isContain('Value "1" is less than "3"', $out);
        isContain('Value "2" is less than "3"', $out);
        isContain("##teamcity[testSuiteFinished name='simple_header.csv'", $out);
    }

    public function testRenderGithub(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        isSame(
            \implode("\n", [
                '::error file=simple_header.csv,line=2::min at column 0:seq%0AValue "1" is less than "3"',
                '',
                '::error file=simple_header.csv,line=3::min at column 0:seq%0AValue "2" is less than "3"',
                '',
            ]),
            $csv->validate()->render(ErrorSuite::RENDER_GITHUB),
        );
    }

    public function testRenderGitlab(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        isSame(
            [
                [
                    'description' => "min at column 0:seq\nValue \"1\" is less than \"3\"",
                    'fingerprint' => 'ec0612c9f1610d440b558fff51bbceed086a0212cdeb14d79d09c8a9bd108487',
                    'severity'    => 'major',
                    'location'    => [
                        'path'  => 'simple_header.csv',
                        'lines' => ['begin' => 2],
                    ],
                ],
                [
                    'description' => "min at column 0:seq\nValue \"2\" is less than \"3\"",
                    'fingerprint' => '51f82750d029c395dec5f2f5c1c4eb841e43c1ea6b8ece9ee31126a3e22620cb',
                    'severity'    => 'major',
                    'location'    => [
                        'path'  => 'simple_header.csv',
                        'lines' => ['begin' => 3],
                    ],
                ],
            ],
            json($csv->validate()->render(ErrorSuite::RENDER_GITLAB))->getArrayCopy(),
        );
    }

    public function testRenderJUnit(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, $this->getRule('seq', 'min', 3));
        isSame(
            \implode("\n", [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<testsuites>',
                '  <testsuite name="simple_header.csv" tests="2">',
                '    <testcase name="min at column 0:seq" file="simple_header.csv" line="2">',
                '      <system-out>Value "1" is less than "3"</system-out>',
                '    </testcase>',
                '    <testcase name="min at column 0:seq" file="simple_header.csv" line="3">',
                '      <system-out>Value "2" is less than "3"</system-out>',
                '    </testcase>',
                '  </testsuite>',
                '</testsuites>',
                '',
            ]),
            $csv->validate()->render(ErrorSuite::RENDER_JUNIT),
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

    private function getRule(?string $columnName, ?string $ruleName, array|bool|float|int|string $options): array
    {
        return ['columns' => [['name' => $columnName, 'rules' => [$ruleName => $options]]]];
    }
}
