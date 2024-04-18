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
use JBZoo\CsvBlueprint\Schema;
use Symfony\Component\Console\Input\InputOption;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DebugSchema extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('debug-schema')
            ->setDescription('Show the internal representation of the schema taking into account presets.')
            ->addOption(
                'schema',
                's',
                InputOption::VALUE_REQUIRED,
                \implode("\n", [
                    'Specify the path to a schema file, supporting YAML, JSON, or PHP formats. ',
                    'Examples: <info>/full/path/name.yml</info>; <info>p/file.yml</info>',
                ]),
            )
            ->addOption(
                'hide-defaults',
                'd',
                InputOption::VALUE_NONE,
                'Hide default values in the output.',
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $decorated = $this->outputMode->getOutput()->isDecorated();

        $schemaFilename = $this->getOptString('schema');
        if (!\file_exists($schemaFilename)) {
            throw new Exception("Schema file not found: {$schemaFilename}");
        }

        $this->_((new Schema($schemaFilename))->dumpAsYamlString($this->getOptBool('hide-defaults'), $decorated));

        return self::SUCCESS;
    }
}
