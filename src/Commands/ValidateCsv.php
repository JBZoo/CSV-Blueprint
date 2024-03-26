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
use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\CsvBlueprint\Exception;
use JBZoo\CsvBlueprint\Schema;
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
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                \implode('', [
                    "Path(s) to schema file(s).\n",
                    'It can be a YAML, JSON or PHP. See examples on GitHub.',
                    'Also, you can specify path in which schema files will be searched ',
                    '(max depth is ' . Utils::MAX_DIRECTORY_DEPTH . ").\n",
                    "Feel free to use glob pattrens. Usage examples: \n",
                    '<info>/full/path/file.yml</info>, ',
                    '<info>p/file.yml</info>, ',
                    '<info>p/*.yml</info>, ',
                    '<info>p/**/*.yml</info>, ',
                    '<info>p/**/name-*.json</info>, ',
                    '<info>**/*.php</info>, ',
                    'etc.',
                ]),
            )
            ->addOption(
                'report',
                'r',
                InputOption::VALUE_REQUIRED,
                "Report output format. Available options:\n" .
                '<info>' . \implode(', ', ErrorSuite::getAvaiableRenderFormats()) . '</info>',
                ErrorSuite::REPORT_DEFAULT,
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
            )
            ->addOption(
                'skip-schema',
                'S',
                InputOption::VALUE_OPTIONAL,
                "Skip schema validation.\n" .
                "If you are sure that the schema is correct, you can skip this check.\n" .
                'Empty value or "yes" will be treated as "true".',
                'no',
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        if ($this->isHumanReadableMode()) {
            $this->_('CSV Blueprint: ' . Utils::getVersion(true));
        }

        if ($this->getOptBool('profile')) {
            \define('PROFILE_MODE', true);
        }

        $csvFilenames = $this->getCsvFilepaths();
        $schemaFilenames = $this->getSchemaFilepaths();
        $matchedFiles = Utils::matchSchemaAndCsvFiles($csvFilenames, $schemaFilenames);

        $this->printHeaderInfo($csvFilenames, $schemaFilenames, $matchedFiles);

        $errorInSchemaCounter = $this->validateSchemas($schemaFilenames);

        [$invalidFiles, $errorInCsvCounter] = $this->validateCsvFiles($matchedFiles);

        return $this->printSummary(
            \count($csvFilenames),
            \count($schemaFilenames),
            $invalidFiles,
            $errorInCsvCounter,
            $errorInSchemaCounter,
            $matchedFiles,
        );
    }

    /**
     * @return SplFileInfo[]
     */
    private function getCsvFilepaths(): array
    {
        return \array_values(Utils::findFiles($this->getOptArray('csv')));
    }

    private function getSchemaFilepaths(): array
    {
        $schemaFilenames = \array_values(Utils::findFiles($this->getOptArray('schema')));

        if (\count($schemaFilenames) === 0) {
            throw new Exception('Schema file(s) not found: ' . \implode('; ', $this->getOptArray('schema')));
        }

        return $schemaFilenames;
    }

    private function isHumanReadableMode(): bool
    {
        return $this->getReportType() !== ErrorSuite::REPORT_GITLAB
            && $this->getReportType() !== ErrorSuite::REPORT_JUNIT
            && $this->getReportType() !== ErrorSuite::REPORT_TEAMCITY;
    }

    private function getReportType(): string
    {
        return $this->getOptString('report', ErrorSuite::RENDER_TABLE, ErrorSuite::getAvaiableRenderFormats());
    }

    private function isQuickMode(): bool
    {
        $value = $this->getOptString('quick');

        return $value === '' || bool($value);
    }

    private function isCheckingSchema(): bool
    {
        $value = $this->getOptString('skip-schema');

        return !($value === '' || bool($value));
    }

    /**
     * @param SplFileInfo[] $schemaFilenames
     */
    private function validateSchemas(array $schemaFilenames): int
    {
        $totalSchemaErrors = new ErrorSuite();

        $schemaErrors = null;
        $quickCheck = $this->isQuickMode();

        if ($this->isCheckingSchema()) {
            $totalFiles = \count($schemaFilenames);

            $this->out("Check schema syntax: {$totalFiles}");

            foreach ($schemaFilenames as $index => $schemaFilename) {
                $prefix = '(' . ((int)$index + 1) . "/{$totalFiles})";
                $path = Utils::printFile($schemaFilename->getPathname());

                if ($quickCheck && $schemaErrors !== null && $schemaErrors->count() > 0) {
                    $this->out("{$prefix} <yellow>Skipped (Quick mode)</yellow>");
                    continue;
                }

                try {
                    $schemaErrors = (new Schema($schemaFilename->getPathname()))->validate($quickCheck);
                    if ($schemaErrors->count() > 0) {
                        $this->out([
                            "{$prefix} Schema: {$path}",
                            "{$prefix} <yellow>Issues:</yellow> {$schemaErrors->count()}",
                        ]);
                        $this->_($schemaErrors->render($this->getReportType()));
                        $totalSchemaErrors->addErrorSuit($schemaErrors);
                    } else {
                        $this->out("{$prefix} <green>OK:</green> {$path}");
                    }
                } catch (Exception $e) {
                    $this->out([
                        "{$prefix} Schema: {$path}",
                        "{$prefix} Exception: <yellow>{$e->getMessage()}</yellow>",
                    ]);
                }
            }

            $this->out('');
        }

        return $totalSchemaErrors->count();
    }

    private function validateCsvFiles(array $matchedFiles): array
    {
        $totalFiles = \count($matchedFiles['found_pairs']);
        $invalidFiles = 0;
        $errorCounter = 0;
        $errorSuite = null;
        $quickCheck = $this->isQuickMode();

        $this->out("CSV file validation: {$totalFiles}");

        foreach ($matchedFiles['found_pairs'] as $index => $pair) {
            [$schema, $csv] = $pair;

            $prefix = '(' . ((int)$index + 1) . "/{$totalFiles})";

            $this->out([
                "{$prefix} Schema: " . Utils::printFile($schema),
                "{$prefix} CSV   : " . Utils::printFile($csv),
            ]);

            if ($quickCheck && $errorSuite !== null && $errorSuite->count() > 0) {
                $this->out("{$prefix} <yellow>Skipped (Quick mode)</yellow>");
                continue;
            }

            $csvFile = new CsvFile($csv, $schema);
            $errorSuite = $csvFile->validate($quickCheck);

            if ($errorSuite->count() > 0) {
                $invalidFiles++;
                $errorCounter += $errorSuite->count();
                $this->out("{$prefix} <yellow>Issues:</yellow> {$errorSuite->count()}");
                $this->_($errorSuite->render($this->getOptString('report')));
            } else {
                $this->out("{$prefix} <green>OK</green>");
            }
        }

        return [$invalidFiles, $errorCounter];
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function printSummary(
        int $totalCsvFiles,
        int $totalSchemaFiles,
        int $invalidCsvFiles,
        int $errorInCsvCounter,
        int $errorInSchemaCounter,
        array $matchedFiles,
    ): int {
        $exitCode = self::SUCCESS;
        if (
            $totalSchemaFiles === 0
            || $errorInCsvCounter > 0
            || $errorInSchemaCounter > 0
            || \count($matchedFiles['schema_without_csv']) > 0
            || \count($matchedFiles['csv_without_schema']) > 0
            || \count($matchedFiles['found_pairs']) === 0
        ) {
            $exitCode = self::FAILURE;
        }

        $this->out(['', 'Summary:']);

        if ($totalSchemaFiles === 0) {
            $this->out('  <red>No schema files found!</red>');
        }

        $this->out(
            '  ' . \count($matchedFiles['found_pairs']) . ' ' .
            'pairs (schema to csv) were found based on `filename_pattern`.',
        );

        if ($errorInSchemaCounter > 0) {
            $this->out("  Found <c>{$errorInSchemaCounter}</c> issues in {$totalSchemaFiles} schemas.");
        } else {
            $this->out("  <green>No issues in {$totalSchemaFiles} schemas.</green>");
        }

        if ($errorInCsvCounter > 0) {
            $this->out(
                "  Found <c>{$errorInCsvCounter}</c> issues in {$invalidCsvFiles} " .
                "out of {$totalCsvFiles} CSV files.",
            );
        } else {
            $this->out("  <green>No issues in {$totalCsvFiles} CSV files.</green>");
        }

        if (\count($matchedFiles['global_schemas']) > 0) {
            $this->out(
                '  <yellow>Schemas have no filename_pattern and are applied to all CSV files found:</yellow>',
            );

            foreach ($matchedFiles['global_schemas'] as $file) {
                $this->out('    - ' . Utils::printFile($file));
            }
        }

        if (isset($matchedFiles['csv_without_schema']) && \count($matchedFiles['csv_without_schema']) > 0) {
            $this->out(
                "  <yellow>No schema was applied to the CSV files (filename_pattern didn't match):</yellow>",
            );

            foreach ($matchedFiles['csv_without_schema'] as $file) {
                $this->out('    - ' . Utils::printFile($file));
            }
        }

        if (isset($matchedFiles['schema_without_csv']) && \count($matchedFiles['schema_without_csv']) > 0) {
            $this->out('  <yellow>Not used schemas:</yellow>');

            foreach ($matchedFiles['schema_without_csv'] as $file) {
                $this->out('    - ' . Utils::printFile($file));
            }
        }

        if ($exitCode === self::SUCCESS) {
            $this->out('  <green>Looks good!</green>');
        }
        $this->out('');

        return $exitCode;
    }

    /**
     * @param SplFileInfo[] $csvFilenames
     * @param SplFileInfo[] $schemaFilenames
     * @param array[]       $matchedFiles
     */
    private function printHeaderInfo(array $csvFilenames, array $schemaFilenames, array $matchedFiles): void
    {
        $validationFlag = $this->isCheckingSchema() ? '' : ' (<c>Validation skipped</c>)';
        $this->out([
            'Found Schemas   : ' . \count($schemaFilenames) . $validationFlag,
            'Found CSV files : ' . \count($csvFilenames),
            'Pairs by pattern: ' . \count($matchedFiles['found_pairs']),
        ]);

        if ($this->isQuickMode()) {
            $this->out('<yellow>Quick mode enabled!</yellow>');
        }

        $this->out('');
    }

    private function out(null|array|string $messge): void
    {
        if ($this->isHumanReadableMode()) {
            $this->_($messge);
        }
    }
}
