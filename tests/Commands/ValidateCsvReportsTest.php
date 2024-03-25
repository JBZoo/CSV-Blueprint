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

namespace JBZoo\PHPUnit\Commands;

use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isNotEmpty;
use function JBZoo\PHPUnit\isSame;

final class ValidateCsvReportsTest extends TestCase
{
    public function testDefault(): void
    {
        $expected = <<<'TXT'
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) Issues: 2
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            | Line  | id:Column        | Rule         | Message                                                              |
            +-------+------------------+--------------+----------------------------------------------------------------------+
            | undef | 2:Float          | is_float     | Value "Qwerty" is not a float number                                 |
            | undef | 4:Favorite color | allow_values | Value "123" is not allowed. Allowed values: ["red", "green", "Blue"] |
            +-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
            
            
            CSV file validation: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) CSV   : ./tests/fixtures/demo.csv
            (1/1) Issues: 10
            +------+------------------+--------------+------------------------- demo.csv -------------------------------------------------------------------+
            | Line | id:Column        | Rule         | Message                                                                                              |
            +------+------------------+--------------+------------------------------------------------------------------------------------------------------+
            | 1    |                  | csv.header   | Columns not found in CSV: "wrong_column_name"                                                        |
            | 6    | 0:Name           | length_min   | The length of the value "Carl" is 4, which is less than the expected "5"                             |
            | 11   | 0:Name           | length_min   | The length of the value "Lois" is 4, which is less than the expected "5"                             |
            | 1    | 1:City           | ag:is_unique | Column has non-unique values. Unique: 9, total: 10                                                   |
            | 2    | 2:Float          | num_max      | The value "4825.185" is greater than the expected "4825.184"                                         |
            | 1    | 2:Float          | ag:nth_num   | The N-th value in the column is "74", which is not equal than the expected "0.001"                   |
            | 6    | 3:Birthday       | date_min     | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |                  |              | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 8    | 3:Birthday       | date_min     | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
            |      |                  |              | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
            | 9    | 3:Birthday       | date_max     | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than  |
            |      |                  |              | the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                               |
            | 5    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]                                |
            +------+------------------+--------------+------------------------- demo.csv -------------------------------------------------------------------+
            
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              Found 2 issues in 1 schemas.
              Found 10 issues in 1 out of 1 CSV files.
            
            
            TXT;

        isSame($expected, self::getReport());
    }

    public function testText(): void
    {
        $expected = <<<'TXT'
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) Issues: 2
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            
            CSV file validation: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) CSV   : ./tests/fixtures/demo.csv
            (1/1) Issues: 10
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            "length_min" at line 6, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            "length_min" at line 11, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 9, total: 10.
            "num_max" at line 2, column "2:Float". The value "4825.185" is greater than the expected "4825.184".
            "ag:nth_num" at line 1, column "2:Float". The N-th value in the column is "74", which is not equal than the expected "0.001".
            "date_min" at line 6, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_min" at line 8, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_max" at line 9, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            "allow_values" at line 5, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              Found 2 issues in 1 schemas.
              Found 10 issues in 1 out of 1 CSV files.
            
            
            TXT;

        isSame($expected, self::getReport('text'));
    }

    public function testGithub(): void
    {
        $expected = <<<'TXT'
            Found Schemas   : 1
            Found CSV files : 1
            Pairs by pattern: 1
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) Issues: 2
            ::error file=./tests/schemas/demo_invalid.yml::is_float at column 2:Float%0A"is_float", column "2:Float". Value "Qwerty" is not a float number.
            
            ::error file=./tests/schemas/demo_invalid.yml::allow_values at column 4:Favorite color%0A"allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            
            CSV file validation: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) CSV   : ./tests/fixtures/demo.csv
            (1/1) Issues: 10
            ::error file=<root>/tests/fixtures/demo.csv,line=1::csv.header at column%0A"csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=6::length_min at column 0:Name%0A"length_min" at line 6, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=11::length_min at column 0:Name%0A"length_min" at line 11, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=1::ag:is_unique at column 1:City%0A"ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 9, total: 10.
            
            ::error file=<root>/tests/fixtures/demo.csv,line=2::num_max at column 2:Float%0A"num_max" at line 2, column "2:Float". The value "4825.185" is greater than the expected "4825.184".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=1::ag:nth_num at column 2:Float%0A"ag:nth_num" at line 1, column "2:Float". The N-th value in the column is "74", which is not equal than the expected "0.001".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=6::date_min at column 3:Birthday%0A"date_min" at line 6, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=8::date_min at column 3:Birthday%0A"date_min" at line 8, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=9::date_max at column 3:Birthday%0A"date_max" at line 9, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            
            ::error file=<root>/tests/fixtures/demo.csv,line=5::allow_values at column 4:Favorite color%0A"allow_values" at line 5, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            
            Summary:
              1 pairs (schema to csv) were found based on `filename_pattern`.
              Found 2 issues in 1 schemas.
              Found 10 issues in 1 out of 1 CSV files.
            
            
            TXT;

        isSame($expected, self::getReport('github'));
    }

    public function testTeamcity(): void
    {
        $expected = <<<'TXT'
            
            ##teamcity[testCount count='2' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo_invalid.yml' flowId='42']
            
            ##teamcity[testStarted name='is_float at column 2:Float' locationHint='php_qn://./tests/schemas/demo_invalid.yml' flowId='42']
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            ##teamcity[testFinished name='is_float at column 2:Float' flowId='42']
            
            ##teamcity[testStarted name='allow_values at column 4:Favorite color' locationHint='php_qn://./tests/schemas/demo_invalid.yml' flowId='42']
            "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            ##teamcity[testFinished name='allow_values at column 4:Favorite color' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo_invalid.yml' flowId='42']
            
            
            ##teamcity[testCount count='10' flowId='42']
            
            ##teamcity[testSuiteStarted name='demo.csv' flowId='42']
            
            ##teamcity[testStarted name='csv.header at column' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            ##teamcity[testFinished name='csv.header at column' flowId='42']
            
            ##teamcity[testStarted name='length_min at column 0:Name' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "length_min" at line 6, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            ##teamcity[testFinished name='length_min at column 0:Name' flowId='42']
            
            ##teamcity[testStarted name='length_min at column 0:Name' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "length_min" at line 11, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            ##teamcity[testFinished name='length_min at column 0:Name' flowId='42']
            
            ##teamcity[testStarted name='ag:is_unique at column 1:City' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 9, total: 10.
            ##teamcity[testFinished name='ag:is_unique at column 1:City' flowId='42']
            
            ##teamcity[testStarted name='num_max at column 2:Float' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "num_max" at line 2, column "2:Float". The value "4825.185" is greater than the expected "4825.184".
            ##teamcity[testFinished name='num_max at column 2:Float' flowId='42']
            
            ##teamcity[testStarted name='ag:nth_num at column 2:Float' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "ag:nth_num" at line 1, column "2:Float". The N-th value in the column is "74", which is not equal than the expected "0.001".
            ##teamcity[testFinished name='ag:nth_num at column 2:Float' flowId='42']
            
            ##teamcity[testStarted name='date_min at column 3:Birthday' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "date_min" at line 6, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            ##teamcity[testFinished name='date_min at column 3:Birthday' flowId='42']
            
            ##teamcity[testStarted name='date_min at column 3:Birthday' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "date_min" at line 8, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            ##teamcity[testFinished name='date_min at column 3:Birthday' flowId='42']
            
            ##teamcity[testStarted name='date_max at column 3:Birthday' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "date_max" at line 9, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            ##teamcity[testFinished name='date_max at column 3:Birthday' flowId='42']
            
            ##teamcity[testStarted name='allow_values at column 4:Favorite color' locationHint='php_qn://<root>/tests/fixtures/demo.csv' flowId='42']
            "allow_values" at line 5, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            ##teamcity[testFinished name='allow_values at column 4:Favorite color' flowId='42']
            
            ##teamcity[testSuiteFinished name='demo.csv' flowId='42']
            
            
            TXT;

        isSame($expected, self::getReport('teamcity'));
    }

    public function testJunit(): void
    {
        // TODO: merge multiple XMLs into one
        $expected = <<<'TXT'
            <?xml version="1.0" encoding="UTF-8"?>
            <testsuites>
              <testsuite name="demo_invalid.yml" tests="2">
                <testcase name="is_float at column 2:Float" file="./tests/schemas/demo_invalid.yml" line="0">
                  <system-out>"is_float", column "2:Float". Value "Qwerty" is not a float number.</system-out>
                </testcase>
                <testcase name="allow_values at column 4:Favorite color" file="./tests/schemas/demo_invalid.yml" line="0">
                  <system-out>"allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].</system-out>
                </testcase>
              </testsuite>
            </testsuites>
            
            <?xml version="1.0" encoding="UTF-8"?>
            <testsuites>
              <testsuite name="demo.csv" tests="10">
                <testcase name="csv.header at column" file="<root>/tests/fixtures/demo.csv" line="1">
                  <system-out>"csv.header" at line 1. Columns not found in CSV: "wrong_column_name".</system-out>
                </testcase>
                <testcase name="length_min at column 0:Name" file="<root>/tests/fixtures/demo.csv" line="6">
                  <system-out>"length_min" at line 6, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".</system-out>
                </testcase>
                <testcase name="length_min at column 0:Name" file="<root>/tests/fixtures/demo.csv" line="11">
                  <system-out>"length_min" at line 11, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".</system-out>
                </testcase>
                <testcase name="ag:is_unique at column 1:City" file="<root>/tests/fixtures/demo.csv" line="1">
                  <system-out>"ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 9, total: 10.</system-out>
                </testcase>
                <testcase name="num_max at column 2:Float" file="<root>/tests/fixtures/demo.csv" line="2">
                  <system-out>"num_max" at line 2, column "2:Float". The value "4825.185" is greater than the expected "4825.184".</system-out>
                </testcase>
                <testcase name="ag:nth_num at column 2:Float" file="<root>/tests/fixtures/demo.csv" line="1">
                  <system-out>"ag:nth_num" at line 1, column "2:Float". The N-th value in the column is "74", which is not equal than the expected "0.001".</system-out>
                </testcase>
                <testcase name="date_min at column 3:Birthday" file="<root>/tests/fixtures/demo.csv" line="6">
                  <system-out>"date_min" at line 6, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".</system-out>
                </testcase>
                <testcase name="date_min at column 3:Birthday" file="<root>/tests/fixtures/demo.csv" line="8">
                  <system-out>"date_min" at line 8, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".</system-out>
                </testcase>
                <testcase name="date_max at column 3:Birthday" file="<root>/tests/fixtures/demo.csv" line="9">
                  <system-out>"date_max" at line 9, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".</system-out>
                </testcase>
                <testcase name="allow_values at column 4:Favorite color" file="<root>/tests/fixtures/demo.csv" line="5">
                  <system-out>"allow_values" at line 5, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].</system-out>
                </testcase>
              </testsuite>
            </testsuites>
            
            
            TXT;

        isSame($expected, self::getReport('junit'));
    }

    public function testGitlab(): void
    {
        // TODO: merge multiple XMLs into one
        $expected = <<<'TXT'
            [
                {
                    "description": "is_float at column 2:Float\n\"is_float\", column \"2:Float\". Value \"Qwerty\" is not a float number.",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": ".\/tests\/schemas\/demo_invalid.yml",
                        "lines": {
                            "begin": 0
                        }
                    }
                },
                {
                    "description": "allow_values at column 4:Favorite color\n\"allow_values\", column \"4:Favorite color\". Value \"123\" is not allowed. Allowed values: [\"red\", \"green\", \"Blue\"].",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": ".\/tests\/schemas\/demo_invalid.yml",
                        "lines": {
                            "begin": 0
                        }
                    }
                }
            ]
            
            [
                {
                    "description": "csv.header at column\n\"csv.header\" at line 1. Columns not found in CSV: \"wrong_column_name\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 1
                        }
                    }
                },
                {
                    "description": "length_min at column 0:Name\n\"length_min\" at line 6, column \"0:Name\". The length of the value \"Carl\" is 4, which is less than the expected \"5\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 6
                        }
                    }
                },
                {
                    "description": "length_min at column 0:Name\n\"length_min\" at line 11, column \"0:Name\". The length of the value \"Lois\" is 4, which is less than the expected \"5\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 11
                        }
                    }
                },
                {
                    "description": "ag:is_unique at column 1:City\n\"ag:is_unique\" at line 1, column \"1:City\". Column has non-unique values. Unique: 9, total: 10.",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 1
                        }
                    }
                },
                {
                    "description": "num_max at column 2:Float\n\"num_max\" at line 2, column \"2:Float\". The value \"4825.185\" is greater than the expected \"4825.184\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 2
                        }
                    }
                },
                {
                    "description": "ag:nth_num at column 2:Float\n\"ag:nth_num\" at line 1, column \"2:Float\". The N-th value in the column is \"74\", which is not equal than the expected \"0.001\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 1
                        }
                    }
                },
                {
                    "description": "date_min at column 3:Birthday\n\"date_min\" at line 6, column \"3:Birthday\". The date of the value \"1955-05-14\" is parsed as \"1955-05-14 00:00:00 +00:00\", which is less than the expected \"1955-05-15 00:00:00 +00:00 (1955-05-15)\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 6
                        }
                    }
                },
                {
                    "description": "date_min at column 3:Birthday\n\"date_min\" at line 8, column \"3:Birthday\". The date of the value \"1955-05-14\" is parsed as \"1955-05-14 00:00:00 +00:00\", which is less than the expected \"1955-05-15 00:00:00 +00:00 (1955-05-15)\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 8
                        }
                    }
                },
                {
                    "description": "date_max at column 3:Birthday\n\"date_max\" at line 9, column \"3:Birthday\". The date of the value \"2010-07-20\" is parsed as \"2010-07-20 00:00:00 +00:00\", which is greater than the expected \"2009-01-01 00:00:00 +00:00 (2009-01-01)\".",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 9
                        }
                    }
                },
                {
                    "description": "allow_values at column 4:Favorite color\n\"allow_values\" at line 5, column \"4:Favorite color\". Value \"blue\" is not allowed. Allowed values: [\"red\", \"green\", \"Blue\"].",
                    "fingerprint": "_replaced_",
                    "severity": "major",
                    "location": {
                        "path": "<root>\/tests\/fixtures\/demo.csv",
                        "lines": {
                            "begin": 5
                        }
                    }
                }
            ]
            
            
            TXT;

        isSame($expected, self::getReport('gitlab'));
    }

    private static function getReport(?string $reportType = null): string
    {
        $options = [
            'csv'    => './tests/**/demo.csv',
            'schema' => Tools::DEMO_YML_INVALID,
        ];

        if ($reportType !== null) {
            $options['report'] = $reportType;
        }

        [$output, $exitCode] = Tools::virtualExecution('validate:csv', $options);
        isSame(1, $exitCode, $output);

        $output = \str_replace([
            \str_replace('/', '\/', PROJECT_ROOT),
            PROJECT_ROOT,
        ], '<root>', $output);

        $output = \preg_replace('/\"fingerprint\".*/', '"fingerprint": "_replaced_",', $output);

        isNotEmpty($output);

        return $output;
    }
}
