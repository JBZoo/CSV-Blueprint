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

$classes = require $require;

$index = 0;
$total = \count($classes);

$result = [
    '<?php',
    'if (!\function_exists(\'opcache_compile_file\') || !\ini_get(\'opcache.enable\')) {',
    "    echo 'Opcache is not available.';",
    '    die(1);',
    '}',
    '',
    'if (\'cli\' === \PHP_SAPI && !\ini_get(\'opcache.enable_cli\')) {',
    "    echo 'Opcache is not enabled for CLI applications.';",
    '    die(2);',
    '}',
    '',
];

foreach ($classes as $path) {
    $index++;
    if (\opcache_compile_file($path) === false) {
        throw new Exception("Can't compile file: {$path}");
    }

    $result[] = "\\opcache_compile_file('{$path}');";
}

\file_put_contents(__DIR__ . '/preload.php', \implode("\n", $result));
