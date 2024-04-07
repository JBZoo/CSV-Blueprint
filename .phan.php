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

$default = include __DIR__ . '/vendor/jbzoo/codestyle/src/phan.php';

// Remove SimplifyExpressionPlugin from plugin list
$default['plugins'] = \array_diff($default['plugins'], ['SimplifyExpressionPlugin']);

return \array_merge($default, [
    'directory_list' => [
        'src',

        'vendor/jbzoo/data/src',
        'vendor/jbzoo/cli/src',
        'vendor/jbzoo/utils/src',
        'vendor/jbzoo/ci-report-converter/src',

        'vendor/symfony/console',
        'vendor/symfony/finder',
        'vendor/symfony/yaml',

        'vendor/league/csv/src',
        'vendor/markrogoyski/math-php/src',
        'vendor/respect/validation',
        'vendor/hds-solutions/parallel-sdk',
    ],
]);
