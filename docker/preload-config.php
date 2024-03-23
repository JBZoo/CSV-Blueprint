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

use ClassPreloader\ClassLoader;
use JBZoo\Cli\CliApplication;
use JBZoo\CsvBlueprint\Commands\ValidateCsv;
use JBZoo\Utils\Cli;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

$config = ClassLoader::getIncludes(function (ClassLoader $loader): void {
    require __DIR__ . '/../vendor/autoload.php';
    $loader->register();

    $command = (new CliApplication())->add(new ValidateCsv());
    $buffer  = new BufferedOutput();
    $args    = new StringInput(Cli::build('', [
        'csv'    => './tests/fixtures/*.csv',
        'schema' => './tests/schemas/*.yml',
    ]));

    $command->run($args, $buffer);
});

return $config;
