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
use Symfony\Component\Console\Input\InputOption;

use function JBZoo\Data\yml;

final class CreateSchema extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('create:schema')
            ->setDescription('Generate random CSV file by rules from yml file. Data based on fakerphp/faker library.')
            ->addArgument(
                'rule-file',
                InputArgument::REQUIRED,
                'Path to rule file (yml)',
            )
            ->addOption(
                'seed',
                null,
                InputOption::VALUE_OPTIONAL,
                'Seed for random data generation (fakerphp/faker library)',
            )
            ->addOption(
                'to-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the generated CSV will be saved to the specified file. '
                . 'Otherwise, the result will be output to the console as STDOUT.',
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
