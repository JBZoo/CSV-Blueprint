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

use JBZoo\CsvBlueprint\Workers\WorkerPool;

\define('PATH_ROOT', __DIR__);
require_once PATH_ROOT . '/vendor/autoload.php';

if ('cli' !== \PHP_SAPI) {
    throw new Exception('This script must be run from the command line.');
}

WorkerPool::setBootstrap(
    \file_exists(PATH_ROOT . '/docker/preload.php')
        ? PATH_ROOT . '/docker/preload.php'
        : PATH_ROOT . '/vendor/autoload.php',
);

// Fix for GitHub actions. See action.yml
$_SERVER['argv'] = Utils::fixArgv($_SERVER['argv'] ?? []);
$_SERVER['argc'] = \count($_SERVER['argv']);

Utils::init();

(new CliApplication('CSV Blueprint', Utils::getVersion(true)))
    ->registerCommandsByPath(PATH_ROOT . '/src/Commands', __NAMESPACE__)
    ->run();
