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

return [
    'name'        => 'CSV Blueprint Schema Example',
    'description' => 'This YAML file provides a detailed description and validation rules for CSV files
to be processed by JBZoo/Csv-Blueprint tool. It includes specifications for file name patterns,
CSV formatting options, and extensive validation criteria for individual columns and their values,
supporting a wide range of data validation rules from basic type checks to complex regex validations.
This example serves as a comprehensive guide for creating robust CSV file validations.
',

    'filename_pattern' => '/demo(-\\d+)?\\.csv$/i',

    'csv' => [
        'header'     => true,
        'delimiter'  => ',',
        'quote_char' => '\\',
        'enclosure'  => '"',
        'encoding'   => 'utf-8',
        'bom'        => false,
    ],

    'columns' => [
        [
            'name'        => 'Column Name (header)',
            'description' => 'Lorem ipsum',
            'example'     => 'Some example',

            'rules' => [
                'not_empty'        => true,
                'exact_value'      => 'Some string',
                'allow_values'     => ['y', 'n', ''],
                'not_allow_values' => ['invalid'],

                'regex' => '/^[\\d]{2}$/',

                'length_min'     => 1,
                'length_greater' => 2,
                'length_not'     => 0,
                'length'         => 7,
                'length_less'    => 8,
                'length_max'     => 9,

                'is_trimmed'    => true,
                'is_lowercase'  => true,
                'is_uppercase'  => true,
                'is_capitalize' => true,

                'word_count_min'     => 1,
                'word_count_greater' => 2,
                'word_count_not'     => 0,
                'word_count'         => 7,
                'word_count_less'    => 8,
                'word_count_max'     => 9,

                'contains'      => 'World',
                'contains_none' => ['a', 'b'],
                'contains_one'  => ['a', 'b'],
                'contains_any'  => ['a', 'b'],
                'contains_all'  => ['a', 'b'],
                'starts_with'   => 'prefix ',
                'ends_with'     => ' suffix',

                'num_min'     => 1.0,
                'num_greater' => 2.0,
                'num_not'     => 5.0,
                'num'         => 7.0,
                'num_less'    => 8.0,
                'num_max'     => 9.0,
                'is_int'      => true,
                'is_float'    => true,

                'precision_min'     => 1,
                'precision_greater' => 2,
                'precision_not'     => 0,
                'precision'         => 7,
                'precision_less'    => 8,
                'precision_max'     => 9,

                'date_min'           => '-100 years',
                'date_greater'       => '-99 days',
                'date_not'           => '2006-01-02 15:04:05 -0700 Europe/Rome',
                'date'               => '01 Jan 2000',
                'date_less'          => 'now',
                'date_max'           => '+1 day',
                'date_format'        => 'Y-m-d',
                'is_date'            => true,
                'is_timezone'        => true,
                'is_timezone_offset' => true,
                'is_time'            => true,
                'is_leap_year'       => true,

                'is_bool'          => true,
                'is_uuid'          => true,
                'is_slug'          => true,
                'is_currency_code' => true,
                'is_base64'        => true,
                'is_angle'         => true,

                'is_ip'                   => true,
                'is_ip_v4'                => true,
                'is_ip_v6'                => true,
                'is_ip_private'           => true,
                'is_ip_reserved'          => true,
                'ip_v4_range'             => ['127.0.0.1-127.0.0.5', '127.0.0.0/21'],
                'is_mac_address'          => true,
                'is_domain'               => true,
                'is_public_domain_suffix' => true,
                'is_url'                  => true,
                'is_email'                => true,

                'is_json'               => true,
                'is_latitude'           => true,
                'is_longitude'          => true,
                'is_geohash'            => true,
                'is_cardinal_direction' => true,
                'is_usa_market_name'    => true,

                'is_country_code'  => 'alpha-2',
                'is_language_code' => 'alpha-2',

                'is_file_exists' => true,
                'is_dir_exists'  => true,

                'is_fibonacci'    => true,
                'is_prime_number' => true,
                'is_even'         => true,
                'is_odd'          => true,
                'is_roman'        => true,

                'phone' => 'ALL',

                'is_version'   => true,
                'is_punct'     => true,
                'is_vowel'     => true,
                'is_consonant' => true,
                'is_alnum'     => true,
                'is_alpha'     => true,

                'hash' => 'set_algo',
            ],

            'aggregate_rules' => [
                'is_unique' => true,
                'sorted' => ['asc', 'natural'],

                'first_num_min'     => 1.0,
                'first_num_greater' => 2.0,
                'first_num_not'     => 5.0,
                'first_num'         => 7.0,
                'first_num_less'    => 8.0,
                'first_num_max'     => 9.0,
                'first'             => 'Expected',
                'first_not'         => 'Not expected',

                'nth_num_min'     => [42, 1.0],
                'nth_num_greater' => [42, 2.0],
                'nth_num_not'     => [42, 5.0],
                'nth_num'         => [42, 7.0],
                'nth_num_less'    => [42, 8.0],
                'nth_num_max'     => [42, 9.0],
                'nth'             => [2, 'Expected'],
                'nth_not'         => [2, 'Not expected'],

                'last_num_min'     => 1.0,
                'last_num_greater' => 2.0,
                'last_num_not'     => 5.0,
                'last_num'         => 7.0,
                'last_num_less'    => 8.0,
                'last_num_max'     => 9.0,
                'last'             => 'Expected',
                'last_not'         => 'Not expected',

                'sum_min'     => 1.0,
                'sum_greater' => 2.0,
                'sum_not'     => 5.0,
                'sum'         => 7.0,
                'sum_less'    => 8.0,
                'sum_max'     => 9.0,

                'average_min'     => 1.0,
                'average_greater' => 2.0,
                'average_not'     => 5.0,
                'average'         => 7.0,
                'average_less'    => 8.0,
                'average_max'     => 9.0,

                'count_min'     => 1,
                'count_greater' => 2,
                'count_not'     => 0,
                'count'         => 7,
                'count_less'    => 8,
                'count_max'     => 9,

                'count_empty_min'     => 1,
                'count_empty_greater' => 2,
                'count_empty_not'     => 0,
                'count_empty'         => 7,
                'count_empty_less'    => 8,
                'count_empty_max'     => 9,

                'count_not_empty_min'     => 1,
                'count_not_empty_greater' => 2,
                'count_not_empty_not'     => 0,
                'count_not_empty'         => 7,
                'count_not_empty_less'    => 8,
                'count_not_empty_max'     => 9,

                'count_distinct_min'     => 1,
                'count_distinct_greater' => 2,
                'count_distinct_not'     => 0,
                'count_distinct'         => 7,
                'count_distinct_less'    => 8,

                'count_distinct_max'     => 9,
                'count_positive_min'     => 1,
                'count_positive_greater' => 2,
                'count_positive_not'     => 0,
                'count_positive'         => 7,
                'count_positive_less'    => 8,

                'count_positive_max'     => 9,
                'count_negative_min'     => 1,
                'count_negative_greater' => 2,
                'count_negative_not'     => 0,
                'count_negative'         => 7,
                'count_negative_less'    => 8,

                'count_negative_max' => 9,
                'count_zero_min'     => 1,
                'count_zero_greater' => 2,
                'count_zero_not'     => 0,
                'count_zero'         => 7,
                'count_zero_less'    => 8,

                'count_zero_max'     => 9,
                'count_even_min'     => 1,
                'count_even_greater' => 2,
                'count_even_not'     => 0,
                'count_even'         => 7,
                'count_even_less'    => 8,

                'count_even_max'    => 9,
                'count_odd_min'     => 1,
                'count_odd_greater' => 2,
                'count_odd_not'     => 0,
                'count_odd'         => 7,
                'count_odd_less'    => 8,

                'count_odd_max'       => 9,
                'count_prime_min'     => 1,
                'count_prime_greater' => 2,
                'count_prime_not'     => 0,
                'count_prime'         => 7,
                'count_prime_less'    => 8,
                'count_prime_max'     => 9,

                'median_min'     => 1.0,
                'median_greater' => 2.0,
                'median_not'     => 5.0,
                'median'         => 7.0,
                'median_less'    => 8.0,
                'median_max'     => 9.0,

                'harmonic_mean_min'     => 1.0,
                'harmonic_mean_greater' => 2.0,
                'harmonic_mean_not'     => 5.0,
                'harmonic_mean'         => 7.0,
                'harmonic_mean_less'    => 8.0,
                'harmonic_mean_max'     => 9.0,

                'geometric_mean_min'     => 1.0,
                'geometric_mean_greater' => 2.0,
                'geometric_mean_not'     => 5.0,
                'geometric_mean'         => 7.0,
                'geometric_mean_less'    => 8.0,
                'geometric_mean_max'     => 9.0,

                'contraharmonic_mean_min'     => 1.0,
                'contraharmonic_mean_greater' => 2.0,
                'contraharmonic_mean_not'     => 5.0,
                'contraharmonic_mean'         => 7.0,
                'contraharmonic_mean_less'    => 8.0,
                'contraharmonic_mean_max'     => 9.0,

                'root_mean_square_min'     => 1.0,
                'root_mean_square_greater' => 2.0,
                'root_mean_square_not'     => 5.0,
                'root_mean_square'         => 7.0,
                'root_mean_square_less'    => 8.0,
                'root_mean_square_max'     => 9.0,

                'trimean_min'     => 1.0,
                'trimean_greater' => 2.0,
                'trimean_not'     => 5.0,
                'trimean'         => 7.0,
                'trimean_less'    => 8.0,
                'trimean_max'     => 9.0,

                'interquartile_mean_min'     => 1.0,
                'interquartile_mean_greater' => 2.0,
                'interquartile_mean_not'     => 5.0,
                'interquartile_mean'         => 7.0,
                'interquartile_mean_less'    => 8.0,
                'interquartile_mean_max'     => 9.0,

                'cubic_mean_min'     => 1.0,
                'cubic_mean_greater' => 2.0,
                'cubic_mean_not'     => 5.0,
                'cubic_mean'         => 7.0,
                'cubic_mean_less'    => 8.0,
                'cubic_mean_max'     => 9.0,

                'percentile_min'     => [95.0, 1.0],
                'percentile_greater' => [95.0, 2.0],
                'percentile_not'     => [95.0, 5.0],
                'percentile'         => [95.0, 7.0],
                'percentile_less'    => [95.0, 8.0],
                'percentile_max'     => [95.0, 9.0],

                'quartiles_min'     => ['exclusive', '0%', 1.0],
                'quartiles_greater' => ['inclusive', 'Q1', 2.0],
                'quartiles_not'     => ['exclusive', 'Q2', 5.0],
                'quartiles'         => ['inclusive', 'Q3', 7.0],
                'quartiles_less'    => ['exclusive', '100%', 8.0],
                'quartiles_max'     => ['inclusive', 'IQR', 9.0],

                'midhinge_min'     => 1.0,
                'midhinge_greater' => 2.0,
                'midhinge_not'     => 5.0,
                'midhinge'         => 7.0,
                'midhinge_less'    => 8.0,
                'midhinge_max'     => 9.0,

                'mean_abs_dev_min'     => 1.0,
                'mean_abs_dev_greater' => 2.0,
                'mean_abs_dev_not'     => 5.0,
                'mean_abs_dev'         => 7.0,
                'mean_abs_dev_less'    => 8.0,
                'mean_abs_dev_max'     => 9.0,

                'median_abs_dev_min'     => 1.0,
                'median_abs_dev_greater' => 2.0,
                'median_abs_dev_not'     => 5.0,
                'median_abs_dev'         => 7.0,
                'median_abs_dev_less'    => 8.0,
                'median_abs_dev_max'     => 9.0,

                'population_variance_min'     => 1.0,
                'population_variance_greater' => 2.0,
                'population_variance_not'     => 5.0,
                'population_variance'         => 7.0,
                'population_variance_less'    => 8.0,
                'population_variance_max'     => 9.0,

                'sample_variance_min'     => 1.0,
                'sample_variance_greater' => 2.0,
                'sample_variance_not'     => 5.0,
                'sample_variance'         => 7.0,
                'sample_variance_less'    => 8.0,
                'sample_variance_max'     => 9.0,

                'stddev_min'     => 1.0,
                'stddev_greater' => 2.0,
                'stddev_not'     => 5.0,
                'stddev'         => 7.0,
                'stddev_less'    => 8.0,
                'stddev_max'     => 9.0,

                'stddev_pop_min'     => 1.0,
                'stddev_pop_greater' => 2.0,
                'stddev_pop_not'     => 5.0,
                'stddev_pop'         => 7.0,
                'stddev_pop_less'    => 8.0,
                'stddev_pop_max'     => 9.0,

                'coef_of_var_min'     => 1.0,
                'coef_of_var_greater' => 2.0,
                'coef_of_var_not'     => 5.0,
                'coef_of_var'         => 7.0,
                'coef_of_var_less'    => 8.0,
                'coef_of_var_max'     => 9.0,
            ],
        ],
        [
            'name'  => 'another_column',
            'rules' => ['not_empty' => true],
        ],
        [
            'name'  => 'third_column',
            'rules' => ['not_empty' => true],
        ],
    ],
];
