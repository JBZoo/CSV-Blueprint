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

use function JBZoo\Utils\bool;

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
            )
            ->addOption(
                'quick',
                'Q',
                InputOption::VALUE_OPTIONAL,
                "Immediately terminate the check at the first error found.\n" .
                "Of course it will speed up the check, but you will get only 1 message out of many.\n" .
                "If any error is detected, the utility will return a non-zero exit code.\n" .
                'Empty value or "yes" will be treated as "true".',
                'no',
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $csvFilenames   = $this->getCsvFilepaths();
        $schemaFilename = $this->getSchemaFilepath();
        $quickCheck     = $this->isQuickCheck();
        $totalFiles     = \count($csvFilenames);

        $this->_("Found CSV files: {$totalFiles}");
        $this->_('');

        $errorCounter = 0;
        $invalidFiles = 0;
        $errorSuite   = null;

        foreach ($csvFilenames as $index => $csvFilename) {
            $prefix = '(' . ((int)$index + 1) . "/{$totalFiles})";

            if ($quickCheck && $errorSuite !== null && $errorSuite->count() > 0) {
                $this->_("{$prefix} <yellow>Skipped:</yellow> " . Utils::cutPath($csvFilename->getPathname()));
                continue;
            }

            $csvFile    = new CsvFile($csvFilename->getPathname(), $schemaFilename);
            $errorSuite = $csvFile->validate($quickCheck);

            if ($errorSuite->count() > 0) {
                $invalidFiles++;
                $errorCounter += $errorSuite->count();

                if ($this->isHumanReadableMode()) {
                    $this->_(
                        "{$prefix} <red>Invalid file:</red> " . Utils::cutPath($csvFilename->getPathname()),
                        OutLvl::E,
                    );
                }

                $output = $errorSuite->render($this->getOptString('report'));
                if ($output !== null) {
                    $this->_($output, $this->isHumanReadableMode() ? OutLvl::E : OutLvl::DEFAULT);
                }
            } elseif ($this->isHumanReadableMode()) {
                $this->_("{$prefix} <green>OK:</green> " . Utils::cutPath($csvFilename->getPathname()));
            }
        }

        if ($errorCounter > 0 && $this->isHumanReadableMode()) {
            if ($totalFiles === 1) {
                $errMessage = "<c>Found {$errorCounter} issues in CSV file.</c>";
            } else {
                $errMessage = "<c>Found {$errorCounter} issues in {$invalidFiles} out of {$totalFiles} CSV files.</c>";
            }

            $this->_('');
            $this->_($errMessage, OutLvl::E);

            return self::FAILURE;
        }

        if ($this->isHumanReadableMode()) {
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

        return \array_values($scvFilenames);
    }

    private function getSchemaFilepath(): string
    {
        $schemaFilename = $this->getOptString('schema');

        if (\file_exists($schemaFilename) === false) {
            throw new Exception("Schema file not found: {$schemaFilename}");
        }

        if ($this->isHumanReadableMode()) {
            $this->_('<blue>Schema:</blue> ' . Utils::cutPath($schemaFilename));
        }

        return $schemaFilename;
    }

    private function isHumanReadableMode(): bool
    {
        return $this->getReportType() !== ErrorSuite::REPORT_GITLAB
            && $this->getReportType() !== ErrorSuite::REPORT_JUNIT;
    }

    private function getReportType(): string
    {
        return $this->getOptString('report', ErrorSuite::RENDER_TABLE, ErrorSuite::getAvaiableRenderFormats());
    }

    private function isQuickCheck(): bool
    {
        $value = $this->getOptString('quick');

        return $value === '' || bool($value);
    }
}
