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

namespace JBZoo\PHPUnit\Benchmarks;

use JBZoo\Cli\CliApplication;

\define('PATH_ROOT', \dirname(__DIR__, 2));

require_once PATH_ROOT . '/vendor/autoload.php';

\date_default_timezone_set('UTC');

\set_error_handler(static function ($severity, $message, $file, $line): void {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

(new CliApplication('CSV Blueprint - Benchmarks'))
    ->registerCommandsByPath(PATH_ROOT . '/tests/Benchmarks/Commands', __NAMESPACE__)
    ->setDefaultCommand('create:csv')
    ->run();
