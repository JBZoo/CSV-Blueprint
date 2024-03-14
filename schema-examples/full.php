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
            'rules'       => [
                'not_empty'             => true,
                'exact_value'           => 'Some string',
                'allow_values'          => ['y', 'n', ''],
                'regex'                 => '/^[\\d]{2}$/',
                'length'                => 5,
                'length_min'            => 1,
                'length_max'            => 10,
                'is_trimed'             => true,
                'is_lowercase'          => true,
                'is_uppercase'          => true,
                'is_capitalize'         => true,
                'word_count'            => 10,
                'word_count_min'        => 1,
                'word_count_max'        => 5,
                'contains'              => 'Hello',
                'contains_one'          => ['a', 'b'],
                'contains_all'          => ['a', 'b', 'c'],
                'starts_with'           => 'prefix ',
                'ends_with'             => ' suffix',
                'min'                   => 10,
                'max'                   => 100.5,
                'precision'             => 3,
                'precision_min'         => 2,
                'precision_max'         => 4,
                'date'                  => '2000-01-10',
                'date_format'           => 'Y-m-d',
                'date_min'              => '2000-01-02',
                'date_max'              => '+1 day',
                'is_bool'               => true,
                'is_int'                => true,
                'is_float'              => true,
                'is_ip'                 => true,
                'is_url'                => true,
                'is_email'              => true,
                'is_domain'             => true,
                'is_uuid4'              => true,
                'is_alias'              => true,
                'is_latitude'           => true,
                'is_longitude'          => true,
                'is_geohash'            => true,
                'is_cardinal_direction' => true,
                'is_usa_market_name'    => true,
            ],

            'aggregate_rules' => [
                'unique' => true,
            ],
        ],
        ['name'        => 'another_column'],
        ['name'        => 'third_column'],
        ['description' => 'Column with description only. Undefined header name.'],
    ],
];
