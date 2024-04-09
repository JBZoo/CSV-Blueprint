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

namespace JBZoo\PHPUnit;

use JBZoo\CsvBlueprint\Workers\WorkerPool;

abstract class TestCase extends PHPUnit
{
    protected function setUp(): void
    {
        parent::setUp();

        \date_default_timezone_set('UTC');
        \putenv('COLUMNS=200');
        \chdir(PROJECT_ROOT);
        WorkerPool::setBootstrap(PROJECT_ROOT . '/vendor/autoload.php');
    }
}
