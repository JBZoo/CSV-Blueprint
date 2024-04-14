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

use JBZoo\CsvBlueprint\Analyze\Analyzer;
use JBZoo\CsvBlueprint\Utils;
use Symfony\Component\Console\Input\InputOption;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CreateSchema extends AbstractValidate
{
    protected function configure(): void
    {
        $this
            ->setName('create:schema')
            ->setDescription('Analyze CSV files and suggest a schema based on the data found.')
            ->addOption(
                'csv',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                \implode("\n", [
                    'Specify the path(s) to the CSV files you want to analyze.',
                    'This can include a direct path to a file or a directory to search with a maximum depth of ' .
                    Utils::MAX_DIRECTORY_DEPTH . ' levels.',
                    'Examples: <info>' . \implode('</info>; <info>', [
                        'p/file.csv',
                        'p/*.csv',
                        'p/**/*.csv',
                        'p/**/name-*.csv',
                        '**/*.csv',
                    ]) . '</info>',
                    '',
                ]),
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $this->preparation();

        $csvFilenames = $this->findFiles('csv', true);

        foreach ($csvFilenames as $csvFilename) {
            (new Analyzer($csvFilename->getRealPath()))->analyzeCsv();
        }

        self::dumpPreloader(); // Experimental feature

        return self::SUCCESS;
    }
}
