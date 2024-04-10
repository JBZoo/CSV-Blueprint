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

$files = include_once __DIR__ . '/included_files.php';

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
    '/app/csv-blueprint',
    '/app/csv-blueprint.php',
];

foreach ($files as $path) {
    foreach ($excludes as $exclude) {
        if ($path === $exclude) {
            continue 2;
        }
    }

    $result[] = "\\opcache_compile_file('{$path}');";
    // $result[] = "require_once '{$path}';";
}

\file_put_contents(__DIR__ . '/preload.php', \implode(\PHP_EOL, $result) . \PHP_EOL);
