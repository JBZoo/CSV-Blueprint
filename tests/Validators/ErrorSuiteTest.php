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
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;

use function JBZoo\Data\json;
use function JBZoo\PHPUnit\isSame;

final class ErrorSuiteTest extends TestCase
{
    public function testRenderText(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::getRule('seq', 'num_min', 3));
        isSame(
            '"num_min" at line 2, column "0:seq". ' .
            'The number of the value "1", which is less or equal than the expected "3".' . "\n",
            \strip_tags($csv->validate(true)->render(ErrorSuite::REPORT_TEXT)),
        );

        isSame(
            <<<'TEXT'
                "num_min" at line 2, column "0:seq". The number of the value "1", which is less or equal than the expected "3".
                "num_min" at line 3, column "0:seq". The number of the value "2", which is less or equal than the expected "3".
                
                TEXT,
            \strip_tags($csv->validate()->render(ErrorSuite::REPORT_TEXT)),
        );
    }

    public function testRenderTable(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::getRule('seq', 'num_min', 3));
        isSame(
            <<<'TABLE'
                +------+-----------+---------+------------- simple_header.csv -------------------------------------------+
                | Line | id:Column | Rule    | Message                                                                   |
                +------+-----------+---------+---------------------------------------------------------------------------+
                | 2    | 0:seq     | num_min | The number of the value "1", which is less or equal than the expected "3" |
                +------+-----------+---------+------------- simple_header.csv -------------------------------------------+
                
                TABLE,
            $csv->validate(true)->render(ErrorSuite::RENDER_TABLE),
        );

        isSame(
            <<<'TABLE'
                +------+-----------+---------+------------- simple_header.csv -------------------------------------------+
                | Line | id:Column | Rule    | Message                                                                   |
                +------+-----------+---------+---------------------------------------------------------------------------+
                | 2    | 0:seq     | num_min | The number of the value "1", which is less or equal than the expected "3" |
                | 3    | 0:seq     | num_min | The number of the value "2", which is less or equal than the expected "3" |
                +------+-----------+---------+------------- simple_header.csv -------------------------------------------+
                
                TABLE,
            $csv->validate()->render(ErrorSuite::RENDER_TABLE),
        );
    }

    public function testRenderTeamCity(): void
    {
        $csv  = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::getRule('seq', 'num_min', 3));
        $out  = $csv->validate()->render(ErrorSuite::REPORT_TEAMCITY);
        $path = Tools::CSV_SIMPLE_HEADER;

        $expected = <<<'TEAMCITY'

            ##teamcity[testCount count='2' flowId='42']

            ##teamcity[testSuiteStarted name='simple_header.csv' flowId='42']
            
            ##teamcity[testStarted name='num_min at column 0:seq' locationHint='php_qn://./tests/fixtures/simple_header.csv' flowId='42']
            "num_min" at line 2, column "0:seq". The number of the value "1", which is less or equal than the expected "3".
            ##teamcity[testFinished name='num_min at column 0:seq' flowId='42']
            
            ##teamcity[testStarted name='num_min at column 0:seq' locationHint='php_qn://./tests/fixtures/simple_header.csv' flowId='42']
            "num_min" at line 3, column "0:seq". The number of the value "2", which is less or equal than the expected "3".
            ##teamcity[testFinished name='num_min at column 0:seq' flowId='42']
            
            ##teamcity[testSuiteFinished name='simple_header.csv' flowId='42']
            
            TEAMCITY;

        isSame($expected, $out);
    }

    public function testRenderGithub(): void
    {
        $path = Tools::CSV_SIMPLE_HEADER;
        $csv  = new CsvFile($path, Tools::getRule('seq', 'num_min', 3));
        isSame(
            <<<'GITHUB'
                ::error file=./tests/fixtures/simple_header.csv,line=2::num_min at column 0:seq%0A"num_min" at line 2, column "0:seq". The number of the value "1", which is less or equal than the expected "3".

                ::error file=./tests/fixtures/simple_header.csv,line=3::num_min at column 0:seq%0A"num_min" at line 3, column "0:seq". The number of the value "2", which is less or equal than the expected "3".
                
                GITHUB,
            $csv->validate()->render(ErrorSuite::REPORT_GITHUB),
        );

        $path = Tools::DEMO_CSV;
        $csv  = new CsvFile($path, Tools::getAggregateRule('City', 'is_unique', true));
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
        $csv  = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::getRule('seq', 'num_min', 3));
        $path = Tools::CSV_SIMPLE_HEADER;

        $cleanJson = json($csv->validate()->render(ErrorSuite::REPORT_GITLAB))->getArrayCopy();
        unset($cleanJson[0]['fingerprint'], $cleanJson[1]['fingerprint']);

        isSame(
            [
                [
                    'description' => "num_min at column 0:seq\n\"num_min\" at line 2, column \"0:seq\". " .
                        'The number of the value "1", which is less or equal than the expected "3".',
                    'severity' => 'major',
                    'location' => ['path' => $path, 'lines' => ['begin' => 2]],
                ],
                [
                    'description' => "num_min at column 0:seq\n\"num_min\" at line 3, column \"0:seq\". " .
                        'The number of the value "2", which is less or equal than the expected "3".',
                    'severity' => 'major',
                    'location' => ['path' => $path, 'lines' => ['begin' => 3]],
                ],
            ],
            $cleanJson,
        );
    }

    public function testRenderJUnit(): void
    {
        $csv  = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::getRule('seq', 'num_min', 3));
        $path = Tools::CSV_SIMPLE_HEADER;
        isSame(
            <<<'JUNIT'
                <?xml version="1.0" encoding="UTF-8"?>
                <testsuites>
                  <testsuite name="simple_header.csv" tests="2">
                    <testcase name="num_min at column 0:seq" file="./tests/fixtures/simple_header.csv" line="2">
                      <system-out>"num_min" at line 2, column "0:seq". The number of the value "1", which is less or equal than the expected "3".</system-out>
                    </testcase>
                    <testcase name="num_min at column 0:seq" file="./tests/fixtures/simple_header.csv" line="3">
                      <system-out>"num_min" at line 3, column "0:seq". The number of the value "2", which is less or equal than the expected "3".</system-out>
                    </testcase>
                  </testsuite>
                </testsuites>
                
                JUNIT,
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
}
