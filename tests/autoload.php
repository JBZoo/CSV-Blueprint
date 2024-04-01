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

\date_default_timezone_set('UTC');

// main autoload
if ($autoload = \dirname(__DIR__) . '/vendor/autoload.php') {
    require_once $autoload;
} else {
    echo 'Please execute "composer update" !' . \PHP_EOL;
    exit(1);
}

if (\extension_loaded('parallel')) {
    parallel\bootstrap(__DIR__ . '/vendor/autoload.php');
}
