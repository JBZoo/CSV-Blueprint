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

use function JBZoo\Data\yml;

final class ValidateFile extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('validate:file')
            ->setDescription('Validate CSV file by rules from yml file(s)')
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
        $yml = '/Users/smetdenis/Work/projects/jbzoo-csv-validator/tests/rules/example.yml';
        dump(yml($yml));

        return self::SUCCESS;
    }
}
