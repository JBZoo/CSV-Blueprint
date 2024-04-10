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

use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Workers\Tasks\ValidationSchemaTask;
use JBZoo\CsvBlueprint\Workers\WorkerPool;
use Symfony\Component\Console\Input\InputOption;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ValidateSchema extends AbstractValidate
{
    protected function configure(): void
    {
        $this
            ->setName('validate:schema')
            ->setDescription('Validate syntax in schema file(s).')
            ->addOption(
                'schema',
                's',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                \implode("\n", [
                    'Specify the path(s) to the schema file(s), supporting YAML, JSON, or PHP formats. ',
                    'Similar to CSV paths, you can direct to specific files or search directories with glob patterns.',
                    'Examples: <info>' . \implode('</info>; <info>', [
                        '/full/path/name.yml',
                        'p/file.yml',
                        'p/*.yml',
                        'p/**/*.yml',
                        'p/**/name-*.yml',
                        '**/*.yml',
                    ]) . '</info>',
                    '',
                ]),
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $this->preparation();

        $schemas = $this->findFiles('schema');
        $totalFiles = \count($schemas);

        $this->out("Found schemas: {$totalFiles}");
        $this->out('');

        $workerPool = new WorkerPool($this->getNumberOfThreads());
        foreach ($schemas as $schema) {
            $filename = (string)$schema->getRealPath();
            $workerPool->addTask($filename, ValidationSchemaTask::class, [$filename]);
        }

        $foundIssues = 0;
        $index = 0;
        $workerPool->run(
            function (string $filename, ErrorSuite $schemaErrors) use (&$index, &$foundIssues, $totalFiles): void {
                $index++;
                $prefix = self::renderPrefix($index, $totalFiles);
                $coloredPath = Utils::printFile($filename);

                if ($schemaErrors->count() > 0) {
                    $this->renderIssues($prefix, $schemaErrors->count(), $coloredPath);
                    $this->outReport($schemaErrors, 2);
                } else {
                    $this->out("{$prefix}<green>OK</green> {$coloredPath}");
                }

                $this->printDumpOfSchema($filename);

                $foundIssues += $schemaErrors->count();
            },
        );

        self::dumpPreloader();

        return $foundIssues === 0 ? self::SUCCESS : self::FAILURE;
    }
}
