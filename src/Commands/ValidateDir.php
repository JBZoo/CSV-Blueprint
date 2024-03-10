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

namespace JBZoo\CsvBlueprint\Commands;

use JBZoo\Cli\CliCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ValidateDir extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('validate:dir')
            ->setDescription('Validate CSV file(s) by rules from yml file(s)')
            ->addArgument(
                'csv-file-or-dir',
                InputArgument::REQUIRED,
                'Path to CSV file or directory with CSV files',
            )
            ->addArgument(
                'rule-file-or-dir',
                InputArgument::REQUIRED,
                'Path to rule file (yml) or directory with rule files',
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
