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
require_once __DIR__ . '/vendor/autoload.php';

if ('cli' !== \PHP_SAPI) {
    throw new Exception('This script must be run from the command line.');
}

// Fix for GitHub actions. See action.yml
$_SERVER['argv'] = Utils::fixArgv($_SERVER['argv'] ?? []);
$_SERVER['argc'] = \count($_SERVER['argv']);

// Set default timezone
\date_default_timezone_set('UTC');

// Convert all errors to exceptions. Looks like we have critical case, and we need to stop or handle it.
// We have to do it becase tool uses 3rd-party libraries, and we can't trust them.
// So, we need to catch all errors and handle them.
\set_error_handler(static function ($severity, $message, $file, $line): void {
    throw new Exception($message, 0, $severity, $file, $line);
});

(new CliApplication('CSV Blueprint', Utils::getVersion(true)))
    ->registerCommandsByPath(PATH_ROOT . '/src/Commands', __NAMESPACE__)
    ->run();
