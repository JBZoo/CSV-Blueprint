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

use function JBZoo\Utils\bool;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CreateSchema extends AbstractValidate
{
    protected function configure(): void
    {
        $this
            ->setName('create-schema')
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
            )
            ->addOption(
                'header',
                'H',
                InputOption::VALUE_OPTIONAL,
                'Force the presence of a header row in the CSV files.',
                'yes',
            )
            ->addOption(
                'lines',
                'l',
                InputOption::VALUE_OPTIONAL,
                'The number of lines to read when detecting parameters. Minimum is 1.',
                10_000,
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $this->preparation(false);

        $csvFilenames = $this->findFiles('csv', true);

        foreach ($csvFilenames as $csvFilename) {
            $csvFilename = (string)$csvFilename->getRealPath();

            $suggestedSchema = (new Analyzer($csvFilename))
                ->analyzeCsv($this->getHeaderOption(), $this->getLinesOption());

            $this->out(
                $suggestedSchema->dumpAsYamlString(
                    true,
                    $this->outputMode->getOutput()->isDecorated(),
                    Utils::cutPath($csvFilename),
                ),
            );
        }

        self::dumpPreloader(); // Experimental feature

        return self::SUCCESS;
    }

    /**
     * Retrieves the value of the header option.
     * @return null|bool The value of the header option. Returns null if the header is set to 'auto'.
     */
    private function getHeaderOption(): ?bool
    {
        $header = \strtolower($this->getOptString('header'));
        if ($header === 'auto') {
            return null;
        }

        return $header === '' || bool($header);
    }

    /**
     * Retrieves the lines option.
     * @return int The value of the lines option. If the option is not set or is less than 1, it returns 1.
     */
    private function getLinesOption(): int
    {
        return \max(1, $this->getOptInt('lines'));
    }
}
