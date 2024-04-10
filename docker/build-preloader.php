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

$require = __DIR__ . '/../vendor/composer/autoload_classmap.php';
$classes = include $require;

$header = <<<'TEXT'
    <?php
    
    declare(strict_types=1);
    
    \set_error_handler(static function ($severity, $message, $file, $line): void {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });
    
    if (!\function_exists('opcache_compile_file') || !\ini_get('opcache.enable')) {
        echo 'Opcache is not available.';
        die(1);
    }
    
    if ('cli' === \PHP_SAPI && !\ini_get('opcache.enable_cli')) {
        echo 'Opcache is not enabled for CLI applications.';
        die(2);
    }
    
    TEXT;

$result = [$header];

$excludes = [
    'vendor/monolog/monolog/src/Monolog/Test/TestCase.php',
    'vendor/symfony/console/DataCollector/CommandDataCollector.php',
    'vendor/symfony/console/Debug/CliRequest.php',
    'vendor/symfony/console/DependencyInjection/AddConsoleCommandPass.php',
    'vendor/symfony/console/EventListener/ErrorListener.php',
    'vendor/symfony/console/Event/ConsoleCommandEvent.php',
    'vendor/symfony/console/Event/ConsoleErrorEvent.php',
    'vendor/symfony/console/Event/ConsoleEvent.php',
    'vendor/symfony/console/Event/ConsoleSignalEvent.php',
    'vendor/symfony/console/Event/ConsoleTerminateEvent.php',
    'vendor/symfony/console/Tester/Constraint/CommandIsSuccessful.php',
    'vendor/symfony/string/Slugger/AsciiSlugger.php',
];

foreach ($classes as $path) {
    foreach ($excludes as $exclude) {
        if (\str_contains($path, $exclude)) {
            continue 2;
        }
    }

    $result[] = "if (!\\opcache_compile_file('{$path}')) { echo 'Not compiled: {$path}'; die(3); }";
}

\file_put_contents(__DIR__ . '/preload.php', \implode(\PHP_EOL, $result) . \PHP_EOL);
