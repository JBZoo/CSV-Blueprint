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
            'name'        => 'csv_header_name',
            'description' => 'Lorem ipsum',
            'rules'       => [
                'not_empty'          => true,
                'exact_value'        => 'Some string',
                'allow_values'       => ['y', 'n', ''],
                'regex'              => '/^[\\d]{2}$/',
                'min_length'         => 1,
                'max_length'         => 10,
                'only_trimed'        => true,
                'only_lowercase'     => true,
                'only_uppercase'     => true,
                'only_capitalize'    => true,
                'word_count'         => 10,
                'min_word_count'     => 1,
                'max_word_count'     => 5,
                'at_least_contains'  => ['a', 'b'],
                'all_must_contain'   => ['a', 'b', 'c'],
                'starts_with'        => 'prefix ',
                'ends_with'          => ' suffix',
                'min'                => 10,
                'max'                => 100.5,
                'precision'          => 3,
                'min_precision'      => 2,
                'max_precision'      => 4,
                'date_format'        => 'Y-m-d',
                'min_date'           => '2000-01-02',
                'max_date'           => '+1 day',
                'is_bool'            => true,
                'is_int'             => true,
                'is_float'           => true,
                'is_ip'              => true,
                'is_url'             => true,
                'is_email'           => true,
                'is_domain'          => true,
                'is_uuid4'           => true,
                'is_latitude'        => true,
                'is_longitude'       => true,
                'is_alias'           => true,
                'cardinal_direction' => true,
                'usa_market_name'    => true,
            ],
        ],
        ['name' => 'another_column'],
    ],
];
