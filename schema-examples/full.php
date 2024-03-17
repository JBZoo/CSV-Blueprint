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
    'filename_pattern' => '/demo(-\\d+)?\\.csv$/i',
    'csv'              => [
        'header'     => true,
        'delimiter'  => ',',
        'quote_char' => '\\',
        'enclosure'  => '"',
        'encoding'   => 'utf-8',
        'bom'        => false,
    ],
    'columns' => [
        0 => [
            'name'        => 'Column Name (header)',
            'description' => 'Lorem ipsum',
            'rules'       => [
                'not_empty'    => true,
                'exact_value'  => 'Some string',
                'allow_values' => ['y', 'n', ''],
                'regex'        => '/^[\\d]{2}$/',

                'length'     => 5,
                'length_not' => 4,
                'length_min' => 1,
                'length_max' => 10,

                'is_trimmed'    => true,
                'is_lowercase'  => true,
                'is_uppercase'  => true,
                'is_capitalize' => true,

                'word_count'     => 5,
                'word_count_not' => 4,
                'word_count_min' => 1,
                'word_count_max' => 10,

                'contains'     => 'Hello',
                'contains_one' => ['a', 'b'],
                'contains_all' => ['a', 'b', 'c'],
                'starts_with'  => 'prefix ',
                'ends_with'    => ' suffix',

                'num'      => 5,
                'num_not'  => 4.123,
                'num_min'  => 1.2e3,
                'num_max'  => -10.123,
                'is_int'   => true,
                'is_float' => true,

                'precision'     => 5,
                'precision_not' => 4,
                'precision_min' => 1,
                'precision_max' => 10,

                'date'        => '01 Jan 2000',
                'date_not'    => '2006-01-02 15:04:05 -0700 Europe/Rome',
                'date_min'    => '+1 day',
                'date_max'    => 'now',
                'date_format' => 'Y-m-d',
                'is_date'     => true,

                'is_bool'   => true,
                'is_ip4'    => true,
                'is_url'    => true,
                'is_email'  => true,
                'is_domain' => true,
                'is_uuid'   => true,
                'is_alias'  => true,

                'is_latitude'           => true,
                'is_longitude'          => true,
                'is_geohash'            => true,
                'is_cardinal_direction' => true,
                'is_usa_market_name'    => true,
            ],

            'aggregate_rules' => [
                'is_unique' => true,

                'sum'     => 5.123,
                'sum_not' => 4.123,
                'sum_min' => 1.123,
                'sum_max' => 10.123,

                'average'     => 5.123,
                'average_not' => 4.123,
                'average_min' => 1.123,
                'average_max' => 10.123,

                'count'     => 5,
                'count_not' => 4,
                'count_min' => 1,
                'count_max' => 10,
            ],
        ],
        ['name'        => 'another_column'],
        ['name'        => 'third_column'],
        ['description' => 'Column with description only. Undefined header name.'],
    ],
];
