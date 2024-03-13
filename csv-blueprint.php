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

use JBZoo\Cli\CliApplication;
use JBZoo\Utils\Cli;

\define('PATH_ROOT', __DIR__);

$vendorPaths = [
    __DIR__ . '/../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

foreach ($vendorPaths as $file) {
    if (\file_exists($file)) {
        \define('JBZOO_AUTOLOAD_FILE', $file);
        break;
    }
}

require_once JBZOO_AUTOLOAD_FILE;

var_dump(Cli::getNumberOfColumns());
exit(1);


\date_default_timezone_set('UTC');

(new CliApplication('CSV Blueprint', '@git-version@'))
    ->registerCommandsByPath(PATH_ROOT . '/src/Commands', __NAMESPACE__)
    ->setLogo(
        <<<'EOF'
             _____            ______ _                       _       _   
            /  __ \           | ___ \ |                     (_)     | |  
            | /  \/_____   __ | |_/ / |_   _  ___ _ __  _ __ _ _ __ | |_ 
            | |   / __\ \ / / | ___ \ | | | |/ _ \ '_ \| '__| | '_ \| __|
            | \__/\__ \\ V /  | |_/ / | |_| |  __/ |_) | |  | | | | | |_ 
             \____/___/ \_/   \____/|_|\__,_|\___| .__/|_|  |_|_| |_|\__|
                                                 | |                     
                                                 |_|                     
            EOF,
    )
    ->run();
