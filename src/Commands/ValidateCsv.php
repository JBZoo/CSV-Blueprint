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
final class ValidateCsv extends AbstractValidate
{
    protected function configure(): void
    {
        $this
            ->setName('validate:csv')
            ->setDescription('Validate CSV file(s) by schema(s).')
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
                    "Path(s) to schema file(s). It can be a YAML, JSON or PHP. See examples on GitHub.\n",
                    'Also, you can specify path in which schema files will be searched ',
                    '(max depth is ' . Utils::MAX_DIRECTORY_DEPTH . ").\n",
                    "Feel free to use glob pattrens. Usage examples: \n",
                    '<info>/full/path/file.yml</info>, ',
                    '<info>p/file.yml</info>, ',
                    '<info>p/*.yml</info>, ',
                    '<info>p/**/*.yml</info>, ',
                    '<info>p/**/name-*.json</info>, ',
                    '<info>**/*.php</info>, ',
                    "etc.\n",
                ]),
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
        $this->preparation();

        $csvFilenames = $this->findFiles('csv', false);
        $schemaFilenames = $this->findFiles('schema', false);
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
                $prefix = AbstractValidate::renderPrefix((int)$index + 1, $totalFiles);
                $schemaPath = Utils::printFile($schemaFilename->getPathname());

                if ($quickCheck && $schemaErrors !== null && $schemaErrors->count() > 0) {
                    $this->out("{$prefix} <yellow>Skipped (Quick mode)</yellow> {$schemaPath}", 2);
                    continue;
                }

                try {
                    $schemaErrors = (new Schema($schemaFilename->getPathname()))->validate($quickCheck);
                    if ($schemaErrors->count() > 0) {
                        $this->out("{$prefix} <yellow>Issues:</yellow> {$schemaErrors->count()} in {$schemaPath}", 2);
                        $this->outReport($schemaErrors, 4);

                        $totalSchemaErrors->addErrorSuit($schemaErrors);
                    } else {
                        $this->out("{$prefix} <green>OK</green> {$schemaPath}", 2);
                    }
                } catch (Exception $e) {
                    $this->out([
                        "{$prefix} Schema: {$schemaPath}",
                        "{$prefix} Exception: <yellow>{$e->getMessage()}</yellow>",
                    ], 2);
                }
            }

            $this->out('');
        }

        return $totalSchemaErrors->count();
    }

    private function validateCsvFiles(array $matchedFiles): array
    {
        $totalFiles = $matchedFiles['count_pairs'];
        $invalidFiles = 0;
        $errorCounter = 0;
        $errorSuite = null;
        $quickCheck = $this->isQuickMode();

        $this->out("CSV file validation: {$totalFiles}");

        $index = 0;
        $isFirst = true;
        foreach ($matchedFiles['found_pairs'] as $schema => $csvs) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $this->out(''); // Add empty line between schema files
            }
            $this->out('Schema: ' . Utils::printFile($schema));
            foreach ($csvs as $csv) {
                $index++;
                $prefix = AbstractValidate::renderPrefix($index, $totalFiles);

                $currentCsvTitle = Utils::printFile($csv, 'blue') . '; Size: ' . Utils::getFileSize($csv);

                if ($quickCheck && $errorSuite !== null && $errorSuite->count() > 0) {
                    $this->out("<yellow>Skipped (Quick mode)</yellow> {$currentCsvTitle}", 2);
                    continue;
                }

                $errorSuite = (new CsvFile($csv, $schema))->validate($quickCheck);

                if ($errorSuite->count() > 0) {
                    $invalidFiles++;
                    $errorCounter += $errorSuite->count();

                    $this->out("{$prefix} <yellow>Issues:</yellow> {$errorSuite->count()} in {$currentCsvTitle}", 2);
                    $this->outReport($errorSuite, 4);
                } else {
                    $this->out("{$prefix} <green>OK</green> {$currentCsvTitle}", 2);
                }
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
        ) {
            $exitCode = self::FAILURE;
        }

        $indent = 2;
        $this->out(['', 'Summary:']);

        if ($totalSchemaFiles === 0) {
            $this->out('<red>No schema files found!</red>', $indent);
        }

        $this->out(
            "{$matchedFiles['count_pairs']} pairs (schema to csv) were found based on `filename_pattern`.",
            $indent,
        );

        if ($errorInSchemaCounter > 0) {
            $this->out("Found <c>{$errorInSchemaCounter}</c> issues in {$totalSchemaFiles} schemas.", $indent);
        } else {
            $this->out("<green>No issues in {$totalSchemaFiles} schemas.</green>", $indent);
        }

        if ($errorInCsvCounter > 0) {
            $this->out(
                "Found <c>{$errorInCsvCounter}</c> issues in {$invalidCsvFiles} " .
                "out of {$totalCsvFiles} CSV files.",
                $indent,
            );
        } else {
            $this->out("<green>No issues in {$totalCsvFiles} CSV files.</green>", $indent);
        }

        if (\count($matchedFiles['global_schemas']) > 0) {
            $this->out(
                '<yellow>Schemas have no filename_pattern and are applied to all CSV files found:</yellow>',
                $indent,
            );

            foreach ($matchedFiles['global_schemas'] as $file) {
                $this->out('  * ' . Utils::printFile($file), $indent);
            }
        }

        if (isset($matchedFiles['csv_without_schema']) && \count($matchedFiles['csv_without_schema']) > 0) {
            $this->out(
                "<yellow>No schema was applied to the CSV files (filename_pattern didn't match):</yellow>",
                $indent,
            );

            foreach ($matchedFiles['csv_without_schema'] as $file) {
                $this->out('  * ' . Utils::printFile($file), $indent);
            }
        }

        if (isset($matchedFiles['schema_without_csv']) && \count($matchedFiles['schema_without_csv']) > 0) {
            $this->out('<yellow>Not used schemas:</yellow>', $indent);

            foreach ($matchedFiles['schema_without_csv'] as $file) {
                $this->out('  * ' . Utils::printFile($file, 'blue'), $indent);
            }
        }

        if ($exitCode === self::SUCCESS) {
            $this->out('<green>Looks good!</green>', $indent);
        }
        $this->out('');

        return $exitCode;
    }

    /**
     * @param SplFileInfo[] $csvFilenames
     * @param SplFileInfo[] $schemaFilenames
     */
    private function printHeaderInfo(array $csvFilenames, array $schemaFilenames, array $matchedFiles): void
    {
        $validationFlag = $this->isCheckingSchema() ? '' : ' (<c>Validation skipped</c>)';
        $this->out([
            'Found Schemas   : ' . \count($schemaFilenames) . $validationFlag,
            'Found CSV files : ' . \count($csvFilenames),
            'Pairs by pattern: ' . $matchedFiles['count_pairs'],
        ]);

        if ($this->isQuickMode()) {
            $this->out('<yellow>Quick mode enabled!</yellow>');
        }

        $this->out('');
    }
}
