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
use JBZoo\Cli\OutLvl;
use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\CsvBlueprint\Exception;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ValidateCsv extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('validate:csv')
            ->setDescription('Validate CSV file(s) by schema.')
            ->addOption(
                'csv',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                \implode('', [
                    "Path(s) to validate.\n" .
                    'You can specify path in which CSV files will be searched ',
                    '(max depth is ' . Utils::MAX_DIRECTORY_DEPTH . ").\n",
                    "Feel free to use glob pattrens. Usage examples: \n",
                    '<info>/full/path/file.csv</info>, ',
                    '<info>p/file.csv</info>, ',
                    '<info>p/*.csv</info>, ',
                    '<info>p/**/*.csv</info>, ',
                    '<info>p/**/name-*.csv</info>, ',
                    '<info>**/*.csv</info>, ',
                    'etc.',
                ]),
            )
            ->addOption(
                'schema',
                's',
                InputOption::VALUE_REQUIRED,
                "Schema filepath.\n" .
                'It can be a YAML, JSON or PHP. See examples on GitHub.',
            )
            ->addOption(
                'report',
                'r',
                InputOption::VALUE_REQUIRED,
                "Report output format. Available options:\n" .
                '<info>' . \implode(', ', ErrorSuite::getAvaiableRenderFormats()) . '</info>',
                ErrorSuite::RENDER_TABLE,
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $csvFilenames   = $this->getCsvFilepaths();
        $schemaFilename = $this->getSchemaFilepath();
        $this->_('');

        $errorCounter = 0;
        $invalidFiles = 0;
        $totalFiles   = \count($csvFilenames);

        foreach ($csvFilenames as $csvFilename) {
            $csvFile    = new CsvFile($csvFilename->getPathname(), $schemaFilename);
            $errorSuite = $csvFile->validate();

            if ($errorSuite->count() > 0) {
                $invalidFiles++;
                $errorCounter += $errorSuite->count();

                if ($this->isTextMode()) {
                    $this->_('<red>Error</red>: ' . Utils::cutPath($csvFilename->getPathname()), OutLvl::E);
                }
                $output = $errorSuite->render($this->getOptString('report'));
                if ($output !== null) {
                    $this->_($output, $this->isTextMode() ? OutLvl::E : OutLvl::DEFAULT);
                }
            } elseif ($this->isTextMode()) {
                $this->_('<green>OK:</green> ' . Utils::cutPath($csvFilename->getPathname()));
            }
        }

        if ($errorCounter > 0 && $this->isTextMode()) {
            if ($totalFiles === 1) {
                $errMessage = "<c>Found {$errorCounter} issues in CSV file.</c>";
            } else {
                $errMessage = "<c>Found {$errorCounter} issues in {$invalidFiles} out of {$totalFiles} CSV files.</c>";
            }

            $this->_($errMessage, OutLvl::E);

            return self::FAILURE;
        }

        if ($this->isTextMode()) {
            $this->_('<green>Looks good!</green>');
        }

        return self::SUCCESS;
    }

    /**
     * @return SplFileInfo[]
     */
    private function getCsvFilepaths(): array
    {
        $rawInput     = $this->getOptArray('csv');
        $scvFilenames = Utils::findFiles($rawInput);

        if (\count($scvFilenames) === 0) {
            throw new Exception('CSV file(s) not found in path(s): ' . \implode("\n, ", $rawInput));
        }

        return $scvFilenames;
    }

    private function getSchemaFilepath(): string
    {
        $schemaFilename = $this->getOptString('schema');

        if (\file_exists($schemaFilename) === false) {
            throw new Exception("Schema file not found: {$schemaFilename}");
        }

        if ($this->isTextMode()) {
            $this->_('<blue>Schema:</blue> ' . Utils::cutPath($schemaFilename));
        }

        return $schemaFilename;
    }

    private function isTextMode(): bool
    {
        return $this->getReportType() === ErrorSuite::REPORT_TEXT
            || $this->getReportType() === ErrorSuite::REPORT_GITHUB
            || $this->getReportType() === ErrorSuite::REPORT_TEAMCITY
            || $this->getReportType() === ErrorSuite::RENDER_TABLE;
    }

    private function getReportType(): string
    {
        return $this->getOptString('report', ErrorSuite::RENDER_TABLE, ErrorSuite::getAvaiableRenderFormats());
    }
}
