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

use function JBZoo\PHPUnit\isContain;
use function JBZoo\PHPUnit\isSame;

final class CreateSchemaTest extends TestCase
{
    public function testWithoutHeader(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
            'csv'    => './tests/fixtures/demo.csv',
            'header' => 'no',
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/demo.csv"
            name: 'Schema for demo.csv'
            description: |-
              CSV file ./tests/fixtures/demo.csv
              Suggested schema based on the first 10000 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /demo\.csv$/
            csv:
              header: false
            columns:
              - example: Name
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 7
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - example: City
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
            
              - example: Float
                rules:
                  not_empty: true
                  length_min: 1
                  length_max: 8
                  is_trimmed: true
                  is_capitalize: true
                  precision_min: 0
                  precision_max: 4
                aggregate_rules:
                  is_unique: true
            
              - example: Birthday
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 10
                  is_trimmed: true
                  is_capitalize: true
            
              - example: 'Favorite color'
                rules:
                  not_empty: true
                  allow_values:
                    - 'Favorite color'
                    - blue
                    - green
                    - red
                  length_min: 3
                  length_max: 14
                  is_trimmed: true
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);

        \file_put_contents(PROJECT_ROOT . '/build/demo.schema.yml', $actual);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => PROJECT_ROOT . '/build/demo.schema.yml',
        ]);
        isContain('Pairs by pattern: 1', $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testWithHeader(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
            'csv'    => './tests/fixtures/demo.csv',
            'header' => 'true',
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/demo.csv"
            name: 'Schema for demo.csv'
            description: |-
              CSV file ./tests/fixtures/demo.csv
              Suggested schema based on the first 10000 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /demo\.csv$/
            columns:
              - name: Name
                example: Clyde
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 7
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: City
                example: Rivsikgo
                rules:
                  not_empty: true
                  length_min: 6
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
            
              - name: Float
                example: '4825.185'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -200.1
                  num_max: 4825.185
                  precision_min: 0
                  precision_max: 4
                aggregate_rules:
                  is_unique: true
            
              - name: Birthday
                example: '2000-01-01'
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '1955-05-14'
                  date_max: '2010-07-20'
                  is_slug: true
            
              - name: 'Favorite color'
                example: green
                rules:
                  not_empty: true
                  allow_values:
                    - blue
                    - green
                    - red
                  length_min: 3
                  length_max: 5
                  is_trimmed: true
                  is_lowercase: true
                  is_slug: true
                  is_public_domain_suffix: true
                  is_alnum: true
                  is_alpha: true
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);

        \file_put_contents(PROJECT_ROOT . '/build/demo.schema.yml', $actual);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => PROJECT_ROOT . '/build/demo.schema.yml',
        ]);
        isContain('Pairs by pattern: 1', $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testWithHeaderComplex(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
            'csv'    => './tests/fixtures/complex_header.csv',
            'header' => 'true',
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/complex_header.csv"
            name: 'Schema for complex_header.csv'
            description: |-
              CSV file ./tests/fixtures/complex_header.csv
              Suggested schema based on the first 10000 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /complex_header\.csv$/
            columns:
              - name: seq
                example: '1'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_int: true
                  num_min: 1
                  num_max: 100
                  is_angle: true
                  is_longitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: bool
                example: 'true'
                rules:
                  not_empty: true
                  allow_values:
                    - 'False'
                    - 'True'
                    - 'false'
                    - 'true'
                  length_min: 4
                  length_max: 5
                  is_trimmed: true
                  is_bool: true
                  is_alnum: true
                  is_alpha: true
            
              - name: yn
                example: 'N'
                rules:
                  not_empty: true
                  allow_values:
                    - 'N'
                    - 'Y'
                  length: 1
                  is_trimmed: true
                  is_uppercase: true
                  is_consonant: true
                  is_alnum: true
                  is_alpha: true
            
              - name: integer
                example: '577928'
                rules:
                  not_empty: false
                  is_trimmed: true
                  is_int: true
                aggregate_rules:
                  is_unique: true
            
              - name: float
                example: '-308500353777.664'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -896172733707.06
                  num_max: 863717712252.11
                  precision_min: 0
                  precision_max: 4
                aggregate_rules:
                  is_unique: true
            
              - name: name/first
                example: Emma
                rules:
                  not_empty: true
                  length_min: 3
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
            
              - name: date
                example: 2042/11/18
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '2024-03-04'
                  date_max: '2124-05-22'
                aggregate_rules:
                  is_unique: true
            
              - name: gender
                example: Female
                rules:
                  not_empty: true
                  allow_values:
                    - Female
                    - Male
                  length_min: 4
                  length_max: 6
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
            
              - name: email
                example: naduka@jamci.kw
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 20
                  is_trimmed: true
                  is_lowercase: true
                  precision_min: 2
                  precision_max: 3
                  is_email: true
                aggregate_rules:
                  is_unique: true
            
              - name: guid
                example: 2feb87a1-a0c2-57f7-82d3-a5eec01cea41
                rules:
                  not_empty: true
                  is_lowercase: true
                  is_uuid: true
                aggregate_rules:
                  is_unique: true
            
              - name: latitude
                example: '-27.94845'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -89.5128
                  num_max: 89.88597
                  precision_min: 3
                  precision_max: 5
                  is_latitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: longitude
                example: '-143.16108'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -178.20241
                  num_max: 168.90054
                  precision_min: 3
                  precision_max: 5
                  is_longitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: sentence
                example: 'En afu emoharhin itu me rectoge gacoseh tob taug raf tet oh hettulob gom tafba no loka.'
                rules:
                  not_empty: true
                  length_min: 7
                  length_max: 113
                  is_capitalize: true
                  is_sentence: true
                aggregate_rules:
                  is_unique: true
            
              - name: empty_string
                rules:
                  exact_value: ''
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);

        \file_put_contents(PROJECT_ROOT . '/build/demo.schema.yml', $actual);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', [
            'csv'    => './tests/fixtures/complex_header.csv',
            'schema' => PROJECT_ROOT . '/build/demo.schema.yml',
        ]);
        isContain('Pairs by pattern: 1', $actual);
        isSame(0, $exitCode, $actual);
    }

    public function testBigComplex(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
            'csv'    => './tests/fixtures/big_header.csv',
            'header' => 'true',
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/big_header.csv"
            name: 'Schema for big_header.csv'
            description: |-
              CSV file ./tests/fixtures/big_header.csv
              Suggested schema based on the first 10000 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /big_header\.csv$/
            columns:
              - name: age
                example: '19'
                rules:
                  not_empty: true
                  length: 2
                  is_trimmed: true
                  is_int: true
                  num_min: 19
                  num_max: 65
                  is_angle: true
                  is_latitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: alpha
                example: EPZYEIbgkOIilbOlTLiT
                rules:
                  not_empty: true
                  length_min: 9
                  length_max: 20
                  is_trimmed: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: alpha(5)
                example: AyXUN
                rules:
                  not_empty: true
                  length: 5
                  is_trimmed: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: birthday
                example: 10/4/1994
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '1975-03-13'
                  date_max: '2005-11-12'
                aggregate_rules:
                  is_unique: true
            
              - name: bool
                example: 'false'
                rules:
                  not_empty: true
                  allow_values:
                    - 'false'
                    - 'true'
                  length_min: 4
                  length_max: 5
                  is_trimmed: true
                  is_lowercase: true
                  is_bool: true
                  is_slug: true
                  is_alnum: true
                  is_alpha: true
            
              - name: char
                example: M
                rules:
                  not_empty: true
                  length: 1
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
            
              - name: city
                example: Luheez
                rules:
                  not_empty: true
                  length_min: 6
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: ccnumber
                example: '6378806183401521'
                rules:
                  not_empty: true
                  length: 16
                  is_trimmed: true
                  is_int: true
                  num_min: 3528050390503177
                  num_max: 6378806183401521
                  is_luhn: true
                  hash: fnv164
                aggregate_rules:
                  is_unique: true
            
              - name: date
                example: 09/06/1970
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '1907-02-13'
                  date_max: '2046-08-21'
                aggregate_rules:
                  is_unique: true
            
              - name: date(2)
                example: 21/09/2040
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
            
              - name: date(3)
                example: 2055/04/15
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '2044-07-23'
                  date_max: '2114-03-25'
                aggregate_rules:
                  is_unique: true
            
              - name: date(4)
                example: '20940316'
                rules:
                  not_empty: true
                  length: 8
                  is_trimmed: true
                  is_int: true
                  num_min: 20400529
                  num_max: 21210323
                  is_date: true
                  date_min: '2040-05-29'
                  date_max: '2121-03-23'
                  hash: crc32
                aggregate_rules:
                  is_unique: true
            
              - name: digit
                example: '6757447'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_int: true
                  num_min: 97008
                  num_max: 9606682456230854
                aggregate_rules:
                  is_unique: true
            
              - name: digit(5)
                example: '28871'
                rules:
                  not_empty: true
                  length: 5
                  is_trimmed: true
                  is_int: true
                  num_min: 7570
                  num_max: 72685
                aggregate_rules:
                  is_unique: true
            
              - name: dollar
                example: $6162.12
                rules:
                  not_empty: true
                  length: 8
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
            
              - name: domain
                example: jubup.mv
                rules:
                  not_empty: true
                  length_min: 5
                  length_max: 11
                  is_trimmed: true
                  is_lowercase: true
                aggregate_rules:
                  is_unique: true
            
              - name: email
                example: ca@bi.sh
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 17
                  is_trimmed: true
                  is_lowercase: true
                  is_email: true
                aggregate_rules:
                  is_unique: true
            
              - name: first
                example: Clifford
                rules:
                  not_empty: true
                  length_min: 5
                  length_max: 8
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: float
                example: '-188143105579.4176'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -737957234553.65
                  num_max: 717109701954.76
                  precision_min: 1
                  precision_max: 4
                aggregate_rules:
                  is_unique: true
            
              - name: gender
                example: Male
                rules:
                  not_empty: true
                  allow_values:
                    - Female
                    - Male
                  length_min: 4
                  length_max: 6
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
            
              - name: guid
                example: f3b79bb7-fc3c-5312-904b-284ce93e943e
                rules:
                  not_empty: true
                  is_lowercase: true
                  is_uuid: true
                aggregate_rules:
                  is_unique: true
            
              - name: integer
                example: '-489624'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_int: true
                  num_min: -735829
                  num_max: 951555
                aggregate_rules:
                  is_unique: true
            
              - name: last
                example: Jennings
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 8
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: latitude
                example: '-65.42206'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -65.42206
                  num_max: 62.54775
                  precision: 5
                  is_latitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: longitude
                example: '94.97258'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_float: true
                  num_min: -98.92791
                  num_max: 132.59665
                  precision_min: 4
                  precision_max: 5
                  is_longitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: mi
                example: L
                rules:
                  not_empty: true
                  length: 1
                  is_trimmed: true
                  is_uppercase: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: name
                example: 'Birdie Dean'
                rules:
                  not_empty: true
                  length_min: 10
                  length_max: 16
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                aggregate_rules:
                  is_unique: true
            
              - name: natural
                example: '6141664873676800'
                rules:
                  not_empty: true
                  is_trimmed: true
                  is_int: true
                  num_min: 262241641299968
                  num_max: 9003754122641408
                  is_even: true
                aggregate_rules:
                  is_unique: true
            
              - name: natural(5)
                example: '5'
                rules:
                  not_empty: true
                  allow_values:
                    - '0'
                    - '1'
                    - '2'
                    - '3'
                    - '5'
                  length: 1
                  is_trimmed: true
                  is_int: true
                  num_min: 0
                  num_max: 5
                  is_angle: true
                  is_latitude: true
            
              - name: paragraph
                example: 'Uz rahiluz hac sed awnop jimsufo pebob kebu jobon rac igowe icoseta heiz cawsidkiv rabod bak rohihaz. Bupinda wiz jiebavih jowomi linek hibetpok wopi fobagte ro dimsogow fusil jiilo badma saci tifi somvumdu tidudot ibueb. Da podu ijme uto ucobera nuw ecufagam mujuun bic vim mi jo dip ranuzi favib mo gu mipoh.'
                rules:
                  not_empty: true
                  length_min: 274
                  length_max: 676
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                  precision_min: 183
                  precision_max: 580
                aggregate_rules:
                  is_unique: true
            
              - name: phone
                example: '(214) 682-4113'
                rules:
                  not_empty: true
                  length: 14
                  is_trimmed: true
                  is_sentence: true
                aggregate_rules:
                  is_unique: true
            
              - name: pick(RED|BLUE|YELLOW|GREEN|WHITE)
                example: WHITE
                rules:
                  not_empty: true
                  allow_values:
                    - BLUE
                    - GREEN
                    - RED
                    - WHITE
                    - YELLOW
                  length_min: 3
                  length_max: 6
                  is_trimmed: true
                  is_uppercase: true
                  is_alnum: true
                  is_alpha: true
            
              - name: postal
                example: 'A5O 6P5'
                rules:
                  not_empty: true
                  length: 7
                  is_trimmed: true
                  is_uppercase: true
                  is_sentence: true
                aggregate_rules:
                  is_unique: true
            
              - name: province
                example: AB
                rules:
                  not_empty: true
                  allow_values:
                    - AB
                    - MB
                    - NL
                    - NS
                    - PE
                  length: 2
                  is_trimmed: true
                  is_uppercase: true
                  is_alnum: true
                  is_alpha: true
            
              - name: seq
                example: '1'
                rules:
                  not_empty: true
                  length: 1
                  is_trimmed: true
                  is_int: true
                  num_min: 1
                  num_max: 9
                  is_angle: true
                  is_latitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: seq(50)
                example: '50'
                rules:
                  not_empty: true
                  length: 2
                  is_trimmed: true
                  is_int: true
                  num_min: 50
                  num_max: 58
                  is_angle: true
                  is_latitude: true
                aggregate_rules:
                  is_unique: true
            
              - name: sentence
                example: 'Me cenuz la iwiluse gu na panu jazigca asafu horte gur va atautjen.'
                rules:
                  not_empty: true
                  length_min: 67
                  length_max: 109
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                aggregate_rules:
                  is_unique: true
            
              - name: state
                example: CT
                rules:
                  not_empty: true
                  length: 2
                  is_trimmed: true
                  is_uppercase: true
                  is_alnum: true
                  is_alpha: true
            
              - name: street
                example: 'Bake Path'
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 13
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                aggregate_rules:
                  is_unique: true
            
              - name: string
                example: ']@W$eCD'
                rules:
                  not_empty: true
                  length_min: 5
                  length_max: 18
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
            
              - name: string(5)
                example: NfP(J
                rules:
                  not_empty: true
                  length: 5
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
            
              - name: word
                example: zuc
                rules:
                  not_empty: true
                  length_min: 2
                  length_max: 7
                  is_trimmed: true
                  is_lowercase: true
                  is_slug: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
            
              - name: yn
                example: 'Y'
                rules:
                  not_empty: true
                  allow_values:
                    - 'N'
                    - 'Y'
                  length: 1
                  is_trimmed: true
                  is_uppercase: true
                  is_consonant: true
                  is_alnum: true
                  is_alpha: true
            
              - name: zip
                example: '70604'
                rules:
                  not_empty: true
                  length: 5
                  is_trimmed: true
                  is_int: true
                  num_min: 27476
                  num_max: 72068
                aggregate_rules:
                  is_unique: true
            
              - name: zip9
                example: 65424-5103
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_slug: true
                aggregate_rules:
                  is_unique: true
            
            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);

        \file_put_contents(PROJECT_ROOT . '/build/demo.schema.yml', $actual);

        [$actual, $exitCode] = Tools::virtualExecution('validate-csv', [
            'csv'    => './tests/fixtures/big_header.csv',
            'schema' => PROJECT_ROOT . '/build/demo.schema.yml',
        ]);
        isContain('Pairs by pattern: 1', $actual);
        isSame(0, $exitCode, $actual);
    }
}
