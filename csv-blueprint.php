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

namespace JBZoo\CsvBlueprint;

\define('PATH_ROOT', __DIR__);

$vendorPaths = [
    __DIR__ . '/../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

foreach ($vendorPaths as $file) {
    if (\file_exists($file)) {
        \define('JBZOO_AUTOLOAD_FILE', $file);
        break;
    }
}

if (\defined('JBZOO_AUTOLOAD_FILE')) {
    require_once JBZOO_AUTOLOAD_FILE;
} else {
    throw new Exception('Cannot find composer autoload file');
}

if ('cli' !== \PHP_SAPI) {
    throw new Exception('This script must be run from the command line.');
}

Utils::init();

(new CliApplication('CSV Blueprint', Utils::getVersion(true)))
    ->registerCommandsByPath(PATH_ROOT . '/src/Commands', __NAMESPACE__)
    ->run();
