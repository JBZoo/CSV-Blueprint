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
            'csv'          => './tests/fixtures/demo.csv',
            'header'       => 'no',
            'check-syntax' => null,
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
                  count: 11
                  count_empty: 0
                  count_not_empty: 11
                  count_distinct: 11

              - example: City
                rules:
                  not_empty: true
                  length_min: 4
                  length_max: 9
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 11
                  count_distinct: 10

              - example: Float
                rules:
                  not_empty: true
                  length_min: 1
                  length_max: 8
                  is_trimmed: true
                  is_capitalize: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 11
                  count_distinct: 11

              - example: Birthday
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 10
                  is_trimmed: true
                  is_capitalize: true
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 11
                  count_distinct: 10

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 11
                  count_distinct: 4

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
            'csv'          => './tests/fixtures/demo.csv',
            'header'       => 'true',
            'check-syntax' => null,
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
                  count: 10
                  count_empty: 0
                  count_not_empty: 10
                  count_distinct: 10

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 10
                  count_distinct: 9

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
                  sum: 4691.3235
                  average: 469.13235
                  count_empty: 0
                  count_not_empty: 10
                  count_distinct: 10
                  count_positive: 7
                  count_negative: 2
                  count_zero: 1
                  count_even: 6
                  count_odd: 4
                  count_prime: 1
                  median: 2.24875
                  geometric_mean: 0.0
                  contraharmonic_mean: 4982.9519578607
                  root_mean_square: 1528.9421054861
                  trimean: 19.624375
                  cubic_mean: 2239.5775672671
                  percentile:
                    - 95.0
                    - 2709.48975
                  midhinge: 37.0
                  mean_abs_dev: 871.21053
                  median_abs_dev: 55.75125
                  population_variance: 2117578.8001118
                  sample_variance: 2352865.3334575
                  stddev: 1533.9052556979
                  stddev_pop: 1455.1902968725
                  coef_of_var: 3.2696642124506
                  interquartile_mean: 20.083083333333

              - name: Birthday
                example: '2000-01-01'
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '1955-05-14'
                  date_max: '2010-07-20'
                  date_format: Y-m-d
                  date_age_min: 14
                  date_age_max: 69
                  is_slug: true
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 10
                  count_distinct: 9

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 10
                  count_distinct: 3

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
            'csv'          => './tests/fixtures/complex_header.csv',
            'header'       => 'true',
            'check-syntax' => null,
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
                  sorted:
                    - asc
                    - numeric
                  sum: 5050.0
                  average: 50.5
                  count: 100
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100
                  count_positive: 100
                  count_negative: 0
                  count_zero: 0
                  count_even: 50
                  count_odd: 50
                  count_prime: 25
                  median: 50.5
                  harmonic_mean: 19.277563597396
                  geometric_mean: 37.992689344834
                  contraharmonic_mean: 67.0
                  root_mean_square: 58.167860541712
                  trimean: 50.5
                  cubic_mean: 63.415329314792
                  percentile:
                    - 95.0
                    - 95.05
                  midhinge: 50.5
                  mean_abs_dev: 25.0
                  median_abs_dev: 25.0
                  population_variance: 833.25
                  sample_variance: 841.66666666667
                  stddev: 29.011491975882
                  stddev_pop: 28.866070047722
                  coef_of_var: 0.57448498962143
                  interquartile_mean: 50.5

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 4

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 2

              - name: integer
                example: '577928'
                rules:
                  not_empty: false
                  is_trimmed: true
                  is_int: true
                  num_min: -970498
                  num_max: 970879
                aggregate_rules:
                  is_unique: true
                  sum: 6403233.0
                  average: 64679.121212121
                  count_empty: 1
                  count_not_empty: 99
                  count_distinct: 100
                  count_positive: 58
                  count_negative: 41
                  count_zero: 0
                  count_even: 56
                  count_odd: 43
                  count_prime: 1
                  median: 105147.0
                  contraharmonic_mean: 5340353.15157
                  root_mean_square: 587715.36376543
                  trimean: 71988.0
                  cubic_mean: 315109.55643787
                  percentile:
                    - 95.0
                    - 950071.4
                  midhinge: 38829.0
                  mean_abs_dev: 504394.33486379
                  median_abs_dev: 518306.0
                  population_variance: 341225960085.16
                  sample_variance: 344707857637.05
                  stddev: 587118.26546024
                  stddev_pop: 584145.49564741
                  coef_of_var: 9.0774001634119
                  interquartile_mean: 97757.411764706

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
                  sum: 3635608941913.7
                  average: 36356089419.137
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100
                  count_positive: 56
                  count_negative: 44
                  count_zero: 0
                  count_even: 47
                  count_odd: 53
                  count_prime: 2
                  median: 101899613451.06
                  contraharmonic_mean: 7566837074160.2
                  root_mean_square: 524500338692.18
                  trimean: 64806172753.92
                  cubic_mean: 240360046201.43
                  percentile:
                    - 95.0
                    - 820969990730.55
                  midhinge: 27712732056.781
                  mean_abs_dev: 454713809094.37
                  median_abs_dev: 459609185294.75
                  population_variance: 2.7377884005036E+23
                  sample_variance: 2.7654428287915E+23
                  stddev: 525874778706.06
                  stddev_pop: 523238798303.76
                  coef_of_var: 14.464558402952
                  interquartile_mean: 58474577243.668

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 91

              - name: date
                example: 2042/11/18
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '2024-03-04'
                  date_max: '2124-05-22'
                  date_format: Y/m/d
                  date_age_min: 0
                  date_age_max: 99
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 2

              - name: email
                example: naduka@jamci.kw
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 20
                  is_trimmed: true
                  is_lowercase: true
                  is_email: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100

              - name: guid
                example: 2feb87a1-a0c2-57f7-82d3-a5eec01cea41
                rules:
                  not_empty: true
                  is_lowercase: true
                  is_uuid: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100

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
                  sum: -1284.9367
                  average: -12.849367
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100
                  count_positive: 40
                  count_negative: 60
                  count_zero: 0
                  count_even: 45
                  count_odd: 55
                  count_prime: 11
                  median: -27.951455
                  geometric_mean: 37.982002577775
                  contraharmonic_mean: -232.802300062
                  root_mean_square: 54.693346870901
                  trimean: -18.87635375
                  percentile:
                    - 95.0
                    - 71.4956695
                  midhinge: -9.8012525
                  mean_abs_dev: 46.68794256
                  median_abs_dev: 43.07734
                  population_variance: 2826.25595964
                  sample_variance: 2854.8039996364
                  stddev: 53.430365894652
                  stddev_pop: 53.162542825189
                  coef_of_var: -4.1582099643237
                  interquartile_mean: -17.8857418

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
                  sum: 0.97034000000008
                  average: 0.0097034000000008
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100
                  count_positive: 52
                  count_negative: 48
                  count_zero: 0
                  count_even: 50
                  count_odd: 50
                  count_prime: 11
                  median: 7.67539
                  geometric_mean: 72.227371737957
                  contraharmonic_mean: 1118310.89382
                  root_mean_square: 104.1701393255
                  trimean: 1.65878625
                  percentile:
                    - 95.0
                    - 147.490272
                  midhinge: -4.3578175
                  mean_abs_dev: 92.416126264
                  median_abs_dev: 91.86574
                  population_variance: 10851.417832938
                  sample_variance: 10961.028114079
                  stddev: 104.69492878874
                  stddev_pop: 104.17013887356
                  coef_of_var: 10789.509737693
                  interquartile_mean: 3.519376

              - name: sentence
                example: 'En afu emoharhin itu me rectoge gacoseh tob taug raf tet oh hettulob gom tafba no loka.'
                rules:
                  not_empty: true
                  length_min: 7
                  length_max: 113
                  is_capitalize: true
                  is_sentence: true
                  word_count_min: 1
                  word_count_max: 18
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 100
                  count_distinct: 100

              - name: empty_string
                rules:
                  exact_value: ''
                aggregate_rules:
                  count_empty: 100
                  count_not_empty: 0
                  count_distinct: 1

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
            'csv'          => './tests/fixtures/big_header.csv',
            'header'       => 'true',
            'check-syntax' => null,
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
                  sum: 351.0
                  average: 39.0
                  count: 9
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 2
                  count_odd: 7
                  count_prime: 3
                  median: 37.0
                  harmonic_mean: 33.462225511451
                  geometric_mean: 36.201994712361
                  contraharmonic_mean: 44.464387464387
                  root_mean_square: 41.642659750682
                  trimean: 38.25
                  cubic_mean: 44.006254850006
                  percentile:
                    - 95.0
                    - 61.0
                  midhinge: 39.5
                  mean_abs_dev: 12.222222222222
                  median_abs_dev: 14.0
                  population_variance: 213.11111111111
                  sample_variance: 239.75
                  stddev: 15.483862567202
                  stddev_pop: 14.598325626972
                  coef_of_var: 0.39702211710774
                  interquartile_mean: 38.2

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  date_age_min: 18
                  date_age_max: 49
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 2

              - name: char
                example: M
                rules:
                  not_empty: true
                  length: 1
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  sum: 4.7851093199549E+16
                  average: 5.3167881332832E+15
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 3
                  count_odd: 6
                  count_prime: 0
                  median: 5.6103407100727E+15
                  harmonic_mean: 5.1097621752616E+15
                  geometric_mean: 5.2174852015907E+15
                  contraharmonic_mean: 5.4970701323939E+15
                  root_mean_square: 5.4061776929488E+15
                  trimean: 5.482533685771E+15
                  cubic_mean: 5.4853075018016E+15
                  percentile:
                    - 95.0
                    - 6.3256972416712E+15
                  midhinge: 5.3547266614693E+15
                  mean_abs_dev: 8.6808283080749E+14
                  median_abs_dev: 7.0684556242219E+14
                  population_variance: 9.5852119351627E+29
                  sample_variance: 1.0783363427058E+30
                  stddev: 1.0384297485655E+15
                  stddev_pop: 9.7904095599534E+14
                  coef_of_var: 0.19531147800773
                  interquartile_mean: 5.5344308670268E+15

              - name: date
                example: 09/06/1970
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '1907-02-13'
                  date_max: '2046-08-21'
                  date_format: m/d/Y
                  date_age_min: 7
                  date_age_max: 117
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

              - name: date(2)
                example: 21/09/2040
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

              - name: date(3)
                example: 2055/04/15
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_date: true
                  date_min: '2044-07-23'
                  date_max: '2114-03-25'
                  date_format: Y/m/d
                  date_age_min: 19
                  date_age_max: 89
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  date_format: Ymd
                  date_age_min: 15
                  date_age_max: 96
                  postal_code: BR
                  hash: crc32
                aggregate_rules:
                  is_unique: true
                  sum: 186866343.0
                  average: 20762927.0
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 4
                  count_odd: 5
                  count_prime: 0
                  median: 20861005.0
                  harmonic_mean: 20759968.815941
                  geometric_mean: 20761447.424545
                  contraharmonic_mean: 20765887.993467
                  root_mean_square: 20764407.443954
                  trimean: 20790797.5
                  cubic_mean: 20765888.658225
                  percentile:
                    - 95.0
                    - 21102320.2
                  midhinge: 20720590.0
                  mean_abs_dev: 217562.44444444
                  median_abs_dev: 229997.0
                  population_variance: 61478891206.222
                  sample_variance: 69163752607.0
                  stddev: 262990.02377847
                  stddev_pop: 247949.37226422
                  coef_of_var: 0.012666327044278
                  interquartile_mean: 20774949.2

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
                  sum: 1.157456389034E+16
                  average: 1.2860626544823E+15
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 5
                  count_odd: 4
                  count_prime: 0
                  median: 29324792.0
                  harmonic_mean: 751106.74589387
                  geometric_mean: 1690917368.0455
                  contraharmonic_mean: 8.3070044557824E+15
                  root_mean_square: 3.268536094523E+15
                  trimean: 2.4598518463029E+14
                  cubic_mean: 4.6315503479153E+15
                  percentile:
                    - 95.0
                    - 6.5500474608088E+15
                  midhinge: 4.9197033993578E+14
                  mean_abs_dev: 1.9999226922094E+15
                  median_abs_dev: 28473547.0
                  population_variance: 9.0293710499455E+30
                  sample_variance: 1.0158042431189E+31
                  stddev: 3.1871684033306E+15
                  stddev_pop: 3.0048911877047E+15
                  coef_of_var: 2.4782372711177
                  interquartile_mean: 557293097095.8

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
                  sum: 292325.0
                  average: 32480.555555556
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 6
                  count_odd: 3
                  count_prime: 1
                  median: 28871.0
                  harmonic_mean: 19080.515562922
                  geometric_mean: 25302.751943673
                  contraharmonic_mean: 46309.854288891
                  root_mean_square: 38783.62792468
                  trimean: 30735.0
                  cubic_mean: 43727.001837069
                  percentile:
                    - 95.0
                    - 66573.4
                  midhinge: 32599.0
                  mean_abs_dev: 17934.962962963
                  median_abs_dev: 19187.0
                  population_variance: 449183305.80247
                  sample_variance: 505331219.02778
                  stddev: 22479.573372904
                  stddev_pop: 21193.945026881
                  coef_of_var: 0.6920932535915
                  interquartile_mean: 29175.2

              - name: dollar
                example: $6162.12
                rules:
                  not_empty: true
                  length: 8
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  sum: -2501755164164.1
                  average: -277972796018.23
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 2
                  count_negative: 7
                  count_zero: 0
                  count_even: 5
                  count_odd: 4
                  count_prime: 0
                  median: -387096037346.51
                  contraharmonic_mean: -958859420983.89
                  root_mean_square: 516272054482.24
                  trimean: -373661706433.33
                  percentile:
                    - 95.0
                    - 451166864953.83
                  midhinge: -360227375520.15
                  mean_abs_dev: 344129861871.53
                  median_abs_dev: 242570025867.67
                  population_variance: 1.8926795891312E+23
                  sample_variance: 2.1292645377726E+23
                  stddev: 461439545094.76
                  stddev_pop: 435049375258.86
                  coef_of_var: -1.6600169214562
                  interquartile_mean: -361059143898.89

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 2

              - name: guid
                example: f3b79bb7-fc3c-5312-904b-284ce93e943e
                rules:
                  not_empty: true
                  is_lowercase: true
                  is_uuid: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  sum: -1344247.0
                  average: -149360.77777778
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 3
                  count_negative: 6
                  count_zero: 0
                  count_even: 4
                  count_odd: 5
                  count_prime: 0
                  median: -489624.0
                  geometric_mean: 585574.8504808
                  contraharmonic_mean: -2810514.2669621
                  root_mean_square: 647904.77453789
                  trimean: -265329.5
                  cubic_mean: 150696.17500519
                  percentile:
                    - 95.0
                    - 881943.8
                  midhinge: -41035.0
                  mean_abs_dev: 568931.40740741
                  median_abs_dev: 223258.0
                  population_variance: 397471954930.62
                  sample_variance: 447155949296.94
                  stddev: 668697.2029977
                  stddev_pop: 630453.76906687
                  coef_of_var: -4.477060262719
                  interquartile_mean: -324923.6

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  sum: -179.9603
                  average: -19.995588888889
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 3
                  count_negative: 6
                  count_zero: 0
                  count_even: 5
                  count_odd: 4
                  count_prime: 2
                  median: -46.06264
                  geometric_mean: 38.812974735917
                  contraharmonic_mean: -129.79496537452
                  root_mean_square: 50.944349710998
                  trimean: -28.2017425
                  percentile:
                    - 95.0
                    - 61.384794
                  midhinge: -10.340845
                  mean_abs_dev: 41.20987037037
                  median_abs_dev: 8.20801
                  population_variance: 2195.503192463
                  sample_variance: 2469.9410915209
                  stddev: 49.698501904191
                  stddev_pop: 46.856196948355
                  coef_of_var: -2.4854732801497
                  interquartile_mean: -36.49114

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
                  sum: 508.54174
                  average: 56.504637777778
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 7
                  count_negative: 2
                  count_zero: 0
                  count_even: 6
                  count_odd: 3
                  count_prime: 0
                  median: 95.86442
                  geometric_mean: 78.990264350801
                  contraharmonic_mean: 182.74070763348
                  root_mean_square: 101.61543924072
                  trimean: 69.0410525
                  cubic_mean: 94.198456106845
                  percentile:
                    - 95.0
                    - 127.170746
                  midhinge: 42.217685
                  mean_abs_dev: 74.86926962963
                  median_abs_dev: 23.16747
                  population_variance: 7132.9234016864
                  sample_variance: 8024.5388268972
                  stddev: 89.57979028161
                  stddev_pop: 84.456636220527
                  coef_of_var: 1.5853528808363
                  interquartile_mean: 86.477182

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

              - name: name
                example: 'Birdie Dean'
                rules:
                  not_empty: true
                  length_min: 10
                  length_max: 16
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                  word_count: 2
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  sum: 4.242884063658E+16
                  average: 4.7143156262866E+15
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 9
                  count_odd: 0
                  count_prime: 0
                  median: 4.4618597867192E+15
                  harmonic_mean: 1.3937078474242E+15
                  geometric_mean: 3.1667759728973E+15
                  contraharmonic_mean: 6.4723050764903E+15
                  root_mean_square: 5.5238110901978E+15
                  trimean: 4.5020178735432E+15
                  cubic_mean: 6.0185922254176E+15
                  percentile:
                    - 95.0
                    - 8.5134163241337E+15
                  midhinge: 4.5421759603671E+15
                  mean_abs_dev: 2.4510460400148E+15
                  median_abs_dev: 2.5017812758036E+15
                  population_variance: 8.2877171359422E+30
                  sample_variance: 9.323681777935E+30
                  stddev: 3.0534704481843E+15
                  stddev_pop: 2.8788395467518E+15
                  coef_of_var: 0.6477017429971
                  interquartile_mean: 4.9191730590777E+15

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
                aggregate_rules:
                  sum: 23.0
                  average: 2.5555555555556
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 5
                  count_positive: 7
                  count_negative: 0
                  count_zero: 2
                  count_even: 4
                  count_odd: 5
                  count_prime: 6
                  median: 2.0
                  geometric_mean: 0.0
                  contraharmonic_mean: 4.0434782608696
                  root_mean_square: 3.2145502536643
                  trimean: 2.375
                  cubic_mean: 3.5974146961893
                  percentile:
                    - 95.0
                    - 5.0
                  midhinge: 2.75
                  mean_abs_dev: 1.7283950617284
                  median_abs_dev: 2.0
                  population_variance: 3.8024691358025
                  sample_variance: 4.2777777777778
                  stddev: 2.0682789409985
                  stddev_pop: 1.9499920860871
                  coef_of_var: 0.80932654212984
                  interquartile_mean: 2.6

              - name: paragraph
                example: 'Uz rahiluz hac sed awnop jimsufo pebob kebu jobon rac igowe icoseta heiz cawsidkiv rabod bak rohihaz. Bupinda wiz jiebavih jowomi linek hibetpok wopi fobagte ro dimsogow fusil jiilo badma saci tifi somvumdu tidudot ibueb. Da podu ijme uto ucobera nuw ecufagam mujuun bic vim mi jo dip ranuzi favib mo gu mipoh.'
                rules:
                  not_empty: true
                  length_min: 274
                  length_max: 676
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                  word_count_min: 48
                  word_count_max: 112
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

              - name: phone
                example: '(214) 682-4113'
                rules:
                  not_empty: true
                  length: 14
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 5

              - name: postal
                example: 'A5O 6P5'
                rules:
                  not_empty: true
                  length: 7
                  is_trimmed: true
                  is_uppercase: true
                  is_sentence: true
                  word_count: 3
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 5

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
                  sorted:
                    - asc
                    - numeric
                  sum: 45.0
                  average: 5.0
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 4
                  count_odd: 5
                  count_prime: 4
                  median: 5.0
                  harmonic_mean: 3.1813718614111
                  geometric_mean: 4.1471662743969
                  contraharmonic_mean: 6.3333333333333
                  root_mean_square: 5.6273143387114
                  trimean: 5.0
                  cubic_mean: 6.0822019955734
                  percentile:
                    - 95.0
                    - 8.6
                  midhinge: 5.0
                  mean_abs_dev: 2.2222222222222
                  median_abs_dev: 2.0
                  population_variance: 6.6666666666667
                  sample_variance: 7.5
                  stddev: 2.7386127875258
                  stddev_pop: 2.5819888974716
                  coef_of_var: 0.54772255750517
                  interquartile_mean: 5.0

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
                  sorted:
                    - asc
                    - numeric
                  sum: 486.0
                  average: 54.0
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 5
                  count_odd: 4
                  count_prime: 1
                  median: 54.0
                  harmonic_mean: 53.876325145744
                  geometric_mean: 53.938181724003
                  contraharmonic_mean: 54.123456790123
                  root_mean_square: 54.061693153902
                  trimean: 54.0
                  cubic_mean: 54.123175609256
                  percentile:
                    - 95.0
                    - 57.6
                  midhinge: 54.0
                  mean_abs_dev: 2.2222222222222
                  median_abs_dev: 2.0
                  population_variance: 6.6666666666667
                  sample_variance: 7.5
                  stddev: 2.7386127875258
                  stddev_pop: 2.5819888974716
                  coef_of_var: 0.050715051620849
                  interquartile_mean: 54.0

              - name: sentence
                example: 'Me cenuz la iwiluse gu na panu jazigca asafu horte gur va atautjen.'
                rules:
                  not_empty: true
                  length_min: 67
                  length_max: 109
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                  word_count_min: 12
                  word_count_max: 18
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

              - name: state
                example: CT
                rules:
                  not_empty: true
                  length: 2
                  is_trimmed: true
                  is_uppercase: true
                  is_usa_state: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 8

              - name: street
                example: 'Bake Path'
                rules:
                  not_empty: true
                  length_min: 8
                  length_max: 13
                  is_trimmed: true
                  is_capitalize: true
                  is_sentence: true
                  word_count: 2
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

              - name: string
                example: ']@W$eCD'
                rules:
                  not_empty: true
                  length_min: 5
                  length_max: 18
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

              - name: string(5)
                example: NfP(J
                rules:
                  not_empty: true
                  length: 5
                  is_trimmed: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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
                aggregate_rules:
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 2

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
                  sum: 445283.0
                  average: 49475.888888889
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9
                  count_positive: 9
                  count_negative: 0
                  count_zero: 0
                  count_even: 6
                  count_odd: 3
                  count_prime: 2
                  median: 49018.0
                  harmonic_mean: 42726.801331982
                  geometric_mean: 46104.095749062
                  contraharmonic_mean: 55693.488300699
                  root_mean_square: 52492.712246559
                  trimean: 48800.125
                  cubic_mean: 55004.957790006
                  percentile:
                    - 95.0
                    - 71482.4
                  midhinge: 48582.25
                  mean_abs_dev: 15391.432098765
                  median_abs_dev: 20579.0
                  population_variance: 307621257.65432
                  sample_variance: 346073914.86111
                  stddev: 18603.061975414
                  stddev_pop: 17539.135031532
                  coef_of_var: 0.37600258213031
                  interquartile_mean: 49527.6

              - name: zip9
                example: 65424-5103
                rules:
                  not_empty: true
                  length: 10
                  is_trimmed: true
                  is_slug: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 9
                  count_distinct: 9

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

    public function testWithHeaderOneLine(): void
    {
        [$actual, $exitCode] = Tools::virtualExecution('create-schema', [
            'csv'          => './tests/fixtures/demo.csv',
            'header'       => 'true',
            'lines'        => 1,
            'check-syntax' => null,
        ]);

        $expected = <<<'YAML'
            # Based on CSV "./tests/fixtures/demo.csv"
            name: 'Schema for demo.csv'
            description: |-
              CSV file ./tests/fixtures/demo.csv
              Suggested schema based on the first 1 lines.
              Please REVIEW IT BEFORE using.
            filename_pattern: /demo\.csv$/
            columns:
              - name: Name
                example: Clyde
                rules:
                  not_empty: true
                  exact_value: Clyde
                  allow_values:
                    - Clyde
                  length: 5
                  is_trimmed: true
                  is_capitalize: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
                  count: 1
                  count_empty: 0
                  count_not_empty: 1
                  count_distinct: 1

              - name: City
                example: Rivsikgo
                rules:
                  not_empty: true
                  exact_value: Rivsikgo
                  allow_values:
                    - Rivsikgo
                  length: 8
                  is_trimmed: true
                  is_capitalize: true
                  is_base64: true
                  is_bic: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 1
                  count_distinct: 1

              - name: Float
                example: '4825.185'
                rules:
                  not_empty: true
                  exact_value: '4825.185'
                  allow_values:
                    - '4825.185'
                  length: 8
                  is_trimmed: true
                  is_float: true
                  num: 4825.185
                  precision: 3
                  is_date: true
                  date: '4825-07-04'
                  date_age: 2800
                aggregate_rules:
                  is_unique: true
                  sum: 4825.185
                  average: 4825.185
                  count_empty: 0
                  count_not_empty: 1
                  count_distinct: 1
                  count_positive: 1
                  count_negative: 0
                  count_zero: 0
                  count_even: 0
                  count_odd: 1
                  count_prime: 0
                  median: 4825.185
                  harmonic_mean: 4825.185
                  geometric_mean: 4825.185
                  contraharmonic_mean: 4825.185
                  root_mean_square: 4825.185
                  trimean: 4825.185
                  cubic_mean: 4825.185
                  percentile:
                    - 95.0
                    - 4825.185
                  midhinge: 4825.185
                  mean_abs_dev: 0.0
                  median_abs_dev: 0.0
                  population_variance: 0.0
                  sample_variance: 0.0
                  stddev: 0.0
                  stddev_pop: 0.0
                  coef_of_var: 0.0
                  interquartile_mean: 4825.185

              - name: Birthday
                example: '2000-01-01'
                rules:
                  not_empty: true
                  exact_value: '2000-01-01'
                  allow_values:
                    - '2000-01-01'
                  length: 10
                  is_trimmed: true
                  is_date: true
                  is_leap_year: true
                  date: '2000-01-01'
                  date_format: Y-m-d
                  date_age: 24
                  is_slug: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 1
                  count_distinct: 1

              - name: 'Favorite color'
                example: green
                rules:
                  not_empty: true
                  exact_value: green
                  allow_values:
                    - green
                  length: 5
                  is_trimmed: true
                  is_lowercase: true
                  is_slug: true
                  is_public_domain_suffix: true
                  is_geohash: true
                  is_alnum: true
                  is_alpha: true
                aggregate_rules:
                  is_unique: true
                  count_empty: 0
                  count_not_empty: 1
                  count_distinct: 1

            YAML;

        isSame($expected, $actual);
        isSame(0, $exitCode, $actual);
    }
}
