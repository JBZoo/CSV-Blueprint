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

        foreach ($this->findFiles('schema') as $index => $file) {
            $prefix = '(' . ((int)$index + 1) . "/{$totalFiles})";

            $filename = $file->getRealPath();
            $coloredPath = Utils::printFile($filename);
            $schemaErrors = new ErrorSuite($filename);

            try {
                $schemaErrors = (new Schema($filename))->validate($this->isQuickMode());
            } catch (ParseException $e) {
                $schemaErrors->addError(new Error('schema.syntax', $e->getMessage(), '', $e->getParsedLine()));
            } catch (\Throwable $e) {
                $schemaErrors->addError(new Error('schema.error', $e->getMessage()));
            }

            if ($schemaErrors->count() > 0) {
                $this->out(["<yellow>Issues:</yellow> {$coloredPath}"]);
                $this->_($schemaErrors->render($this->getReportType()));
            } else {
                $this->out("<green>OK:</green> {$coloredPath}");
            }

            $foundIssues += $schemaErrors->count();
        }

        return $foundIssues === 0 ? self::SUCCESS : self::FAILURE;
    }
}
