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

use JBZoo\CsvBlueprint\Tools\PreloadBuilder;
use JBZoo\Utils\Env;

require_once __DIR__ . '/../vendor/autoload.php';

(new PreloadBuilder(Env::bool('OPCACHE_COMPILER')))
    ->setExcludes([
        \dirname(__DIR__) . '/csv-blueprint',
        \dirname(__DIR__) . '/csv-blueprint.php',
    ])
    ->setFiles(
        \file_exists(__DIR__ . '/included_files.php')
            ? include_once __DIR__ . '/included_files.php'
            : \array_values(include_once __DIR__ . '/../vendor/composer/autoload_classmap.php'),
    )
    ->saveToFile(__DIR__ . '/preload.php', true);
