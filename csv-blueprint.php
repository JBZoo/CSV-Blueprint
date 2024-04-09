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

if ('cli' !== \PHP_SAPI) {
    throw new \RuntimeException('This script must be run from the command line.');
}

\define('PATH_ROOT', __DIR__);
$autoloader = __DIR__ . '/vendor/autoload.php';
require_once $autoloader;

WorkerPool::setBootstrap($autoloader);

// Fix for GitHub actions. See action.yml
$_SERVER['argv'] = Utils::fixArgv($_SERVER['argv'] ?? []);
$_SERVER['argc'] = \count($_SERVER['argv']);

// Set default timezone
\date_default_timezone_set('UTC');

// Convert all errors to exceptions. Looks like we have critical case, and we need to stop or handle it.
// We have to do it becase tool uses 3rd-party libraries, and we can't trust them.
// So, we need to catch all errors and handle them.
\set_error_handler(static function ($severity, $message, $file, $line): void {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

$cliApp = (new CliApplication('CSV Blueprint', Utils::getVersion(true)));
$cliApp->setVersion(Utils::getVersion(false));

$cliApp
    ->registerCommandsByPath(PATH_ROOT . '/src/Commands', __NAMESPACE__)
    ->run();
