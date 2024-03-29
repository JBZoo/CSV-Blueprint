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

// Fix for GitHub actions. See action.yml
$_SERVER['argv'] ??= [];
$_SERVER['argv'] = \array_map('trim', $_SERVER['argv']);

// Extract flags from the command line arguments `extra: --ansi --profile --debug -vvv`
foreach ($_SERVER['argv'] as $key => $arg) {
    if (\str_starts_with($arg, 'extra:')) {
        $arg = \str_replace('extra:', '', $arg);
        $flags = \array_filter(\array_map('trim', \explode(' ', $arg)), static fn ($flag): bool => $flag !== '');
        foreach ($flags as $flag) {
            $_SERVER['argv'][] = $flag;
        }
        unset($_SERVER['argv'][$key]);
    }
}

\define('PATH_ROOT', __DIR__);
require_once __DIR__ . '/vendor/autoload.php';


// Set default timezone
\date_default_timezone_set('UTC');

// Convert all errors to exceptions. Looks like we have critical case, and we need to stop or handle it.
// We have to do it becase tool uses 3rd-party libraries, and we can't trust them.
\set_error_handler(static function ($severity, $message, $file, $line): void {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

$cliApp = (new CliApplication('CSV Blueprint', Utils::getVersion(true)));
$cliApp->setVersion(Utils::getVersion(false));

$cliApp
    ->registerCommandsByPath(PATH_ROOT . '/src/Commands', __NAMESPACE__)
    ->run();
