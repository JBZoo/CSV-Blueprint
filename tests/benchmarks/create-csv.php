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

use Faker\Factory;
use League\Csv\Writer;

function createCsv(int $numOfRows = 1_000_000): void
{
    $writer = Writer::createFromPath(__DIR__ . "/../../build/{$numOfRows}.csv", 'w+');
    $writer->insertOne([
        'id',
        'name',
        'last_name',
        // 'date',
        // 'year',
        // 'month',
        // 'bool',
        // 'latitude',
        // 'longitude',
        // 'address',
        // 'email',
        // 'number',
        // 'float',
        // 'phone',
        // 'postcode',
        // 'color',
        // 'unique',
        // 'word',
        // 'sentence',
        // 'uuid',
    ]);

    $faker = Factory::create();

    for ($i = 0; $i < $numOfRows; $i++) {
        $row = [
            $i,
            $faker->name,
            $faker->lastName,
            // $faker->date('Y-m-d'),
            // $faker->year,
            // $faker->monthName,
            // $faker->boolean ? 'true' : 'false',
            // $faker->latitude,
            // $faker->longitude,
            // $faker->address,
            // $faker->email,
            // $faker->numberBetween(1, 1000),
            // $faker->randomFloat(2, 1, 1000),
            // $faker->phoneNumber,
            // $faker->postcode,
            // $faker->rgbaCssColor,
            // $faker->country,
            // $faker->word,
            // $faker->sentence,
            // $faker->uuid,
        ];

        $writer->insertOne($row);
    }
}

require_once __DIR__ . '/../../vendor/autoload.php';

createCsv((int)$_SERVER['argv'][1]);
