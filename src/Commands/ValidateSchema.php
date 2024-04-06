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
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Exception\ParseException;

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

        $foundIssues = 0;
        $index = 0;
        foreach ($this->findFiles('schema') as $file) {
            $index++;
            $prefix = self::renderPrefix($index, $totalFiles);
            $filename = (string)$file->getRealPath();
            $coloredPath = Utils::printFile($filename);
            $schemaErrors = new ErrorSuite($filename);

            try {
                $schema = new Schema($filename);
                $schemaErrors = $schema->validate($this->isQuickMode());
                $this->printDumpOfSchema($schema);
            } catch (ParseException $e) {
                $schemaErrors->addError(new Error('schema.syntax', $e->getMessage(), '', $e->getParsedLine()));
            } catch (\Throwable $e) {
                $schemaErrors->addError(new Error('schema.error', $e->getMessage()));
            }

            if ($schemaErrors->count() > 0) {
                $this->renderIssues($prefix, $schemaErrors->count(), $coloredPath);
                $this->outReport($schemaErrors, 2);
            } else {
                $this->out("{$prefix}<green>OK</green> {$coloredPath}");
            }

            $foundIssues += $schemaErrors->count();
        }

        return $foundIssues === 0 ? self::SUCCESS : self::FAILURE;
    }
}
