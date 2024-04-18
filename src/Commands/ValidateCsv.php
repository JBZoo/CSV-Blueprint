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

use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Workers\Tasks\ValidationCsvTask;
use JBZoo\CsvBlueprint\Workers\WorkerPool;
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
            ->setName('validate-csv')
            ->setAliases(['validate:csv']) // Legacy name for backward compatibility only
            ->setDescription('Validate CSV file(s) by schema(s).')
            ->addOption(
                'csv',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                \implode("\n", [
                    'Specify the path(s) to the CSV files you want to validate.',
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
                'schema',
                's',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                \implode("\n", [
                    'Specify the path(s) to the schema file(s), supporting YAML, JSON, or PHP formats. ',
                    'Similar to CSV paths, you can direct to specific files or search directories with glob patterns.',
                    'Examples: <info>' . \implode('</info>; <info>', [
                        'p/file.yml',
                        'p/*.yml',
                        'p/**/*.yml',
                        'p/**/name-*.yml',
                        '**/*.yml',
                    ]) . '</info>',
                    '',
                ]),
            )
            ->addOption(
                'skip-schema',
                'S',
                InputOption::VALUE_OPTIONAL,
                \implode("\n", [
                    "Skips schema validation for quicker checks when the schema's correctness is certain.",
                    'Use any non-empty value or "yes" to activate',
                    '',
                ]),
                'no',
            )
            ->addOption(
                'apply-all',
                'a',
                InputOption::VALUE_OPTIONAL,
                \implode("\n", [
                    "Apply all schemas (also without `filename_pattern`) to all CSV files found as global rules.\n",
                    'Available options:',
                    ' - <info>auto</info>: If no glob pattern (*) is used for --schema, the schema is applied to all ' .
                    'found CSV files.',
                    ' - <info>yes|y|1</info>: Apply all schemas to all CSV files, Schemas without `filename_pattern` ' .
                    'are applied as a global rule.',
                    ' - <info>no|n|0</info>: Apply only schemas with not empty `filename_pattern` and ' .
                    'match the CSV files.',
                    'Note. If specify the option `--apply-all` without value, it will be treated as "yes".',
                    '',
                ]),
                'auto',
            )
            ->addOption(
                'quick',
                'Q',
                InputOption::VALUE_OPTIONAL,
                \implode("\n", [
                    'Stops the validation process upon encountering the first error,',
                    'accelerating the check but limiting error visibility.',
                    'Returns a non-zero exit code if any error is detected.',
                    'Enable by setting to any non-empty value or "yes".',
                    '',
                ]),
                'no',
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $this->preparation();

        $csvFilenames = $this->findFiles('csv', false);
        $schemaFilenames = $this->findFiles('schema', false);
        $matchedFiles = Utils::matchSchemaAndCsvFiles($csvFilenames, $schemaFilenames, $this->isApplyAll());

        $this->printHeaderInfo($csvFilenames, $schemaFilenames, $matchedFiles);

        $errorInSchemaCounter = $this->validateSchemas($schemaFilenames);

        [$invalidFiles, $errorInCsvCounter] = $this->validateCsvFiles($matchedFiles);

        $exitCode = $this->printSummary(
            \count($csvFilenames),
            \count($schemaFilenames),
            $invalidFiles,
            $errorInCsvCounter,
            $errorInSchemaCounter,
            $matchedFiles,
        );

        self::dumpPreloader(); // Experimental feature

        return $exitCode;
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
                    $this->out("{$prefix}<yellow>Skipped (Quick mode)</yellow> {$schemaPath}", 2);
                    continue;
                }

                try {
                    $schema = new Schema($schemaFilename->getPathname());
                    $schemaErrors = $schema->validate($quickCheck);
                    if ($schemaErrors->count() > 0) {
                        $this->renderIssues($prefix, $schemaErrors->count(), $schemaPath, 2);
                        $this->outReport($schemaErrors, 4);

                        $totalSchemaErrors->addErrorSuit($schemaErrors);
                    } else {
                        $this->out("{$prefix}<green>OK</green> {$schemaPath}", 2);
                    }
                } catch (Exception $e) {
                    $this->out([
                        "{$prefix}Schema: {$schemaPath}",
                        "{$prefix}Exception: <yellow>{$e->getMessage()}</yellow>",
                    ], 2);
                }
                $this->printDumpOfSchema($schemaFilename->getPathname());
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
        $quickCheck = $this->isQuickMode();

        $workerPool = new WorkerPool($this->getNumberOfThreads());
        foreach ($matchedFiles['found_pairs'] as $schema => $csvs) {
            foreach ($csvs as $csv) {
                $workerPool->addTask("{$csv} => {$schema}", ValidationCsvTask::class, [$csv, $schema, $quickCheck]);
            }
        }

        $this->out("CSV file validation: {$totalFiles}");

        $index = 0;
        $currentSchemaFilename = null;

        $exectionCallback = function (
            string $pair,
            ErrorSuite $errorSuite,
        ) use (
            &$index,
            &$currentSchemaFilename,
            &$invalidFiles,
            &$errorCounter,
            $totalFiles,
            $quickCheck
        ): void {
            $index++;
            $filesAsKey = \explode(' => ', $pair, 2);
            if (\count($filesAsKey) > 1) {
                [$csvFilename, $schemaFilename] = $filesAsKey;
            } else {
                throw new Exception("Invalid pair: {$pair}");
            }

            if ($currentSchemaFilename !== $schemaFilename) {
                $currentSchemaFilename = $schemaFilename;
                if ($index !== 1) { // Add empty line between schema files
                    $this->out('');
                }
                $this->out('Schema: ' . Utils::printFile($schemaFilename));
            }

            $prefix = AbstractValidate::renderPrefix($index, $totalFiles);
            $currentCsvTitle = Utils::printFile($csvFilename, 'blue') . '; Size: ' . Utils::getFileSize($csvFilename);

            if ($quickCheck && $errorSuite->count() > 0) {
                $this->out("<yellow>Skipped (Quick mode)</yellow> {$currentCsvTitle}", 2);
                return;
            }

            if ($errorSuite->count() > 0) {
                $invalidFiles++;
                $errorCounter += $errorSuite->count();

                $this->renderIssues($prefix, $errorSuite->count(), $currentCsvTitle, 2);
                $this->outReport($errorSuite, 4);
            } else {
                $this->out("{$prefix}<green>OK</green> {$currentCsvTitle}", 2);
            }
        };

        $workerPool->run($exectionCallback);

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
        } elseif (!$this->isQuickMode()) {
            $this->out("<green>No issues in {$totalSchemaFiles} schemas.</green>", $indent);
        }

        if ($errorInCsvCounter > 0) {
            $this->out(
                "Found <c>{$errorInCsvCounter}</c> issues in {$invalidCsvFiles} " .
                "out of {$totalCsvFiles} CSV files.",
                $indent,
            );
        } elseif (!$this->isQuickMode()) {
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

    private function isApplyAll(): bool
    {
        $applyAll = \strtolower(\trim($this->getOptString('apply-all')));
        if (\in_array($applyAll, ['', 'yes', 'y', '1'], true)) {
            return true;
        }

        if (\in_array($applyAll, ['no', 'n', '0'], true)) {
            return false;
        }

        if ($applyAll === 'auto') {
            $schemaPatterns = $this->getOptArray('schema');
            foreach ($schemaPatterns as $schema) {
                if (\str_contains($schema, '*')) { // glob pattern found.
                    return false;
                }
            }

            return \count($schemaPatterns) === 1; // Only one schema file found without glob pattern.
        }

        throw new Exception('Invalid value for --apply-all option: ' . $applyAll);
    }

    private function isCheckingSchema(): bool
    {
        $value = $this->getOptString('skip-schema');
        return !($value === '' || bool($value));
    }

    /**
     * @param SplFileInfo[] $csvFilenames
     * @param SplFileInfo[] $schemaFilenames
     */
    private function printHeaderInfo(array $csvFilenames, array $schemaFilenames, array $matchedFiles): void
    {
        $validationFlag = $this->isCheckingSchema() ? '' : ' (<c>Validation skipped</c>)';
        $applyAllFlag = $this->isApplyAll() ? ' (<c>Apply All</c>)' : '';
        $this->out([
            'Found Schemas   : ' . \count($schemaFilenames) . $validationFlag . $applyAllFlag,
            'Found CSV files : ' . \count($csvFilenames),
            'Pairs by pattern: ' . $matchedFiles['count_pairs'],
        ]);

        if ($this->isQuickMode()) {
            $this->out('<yellow>Quick mode enabled!</yellow>');
        }

        $this->out('');
    }
}
