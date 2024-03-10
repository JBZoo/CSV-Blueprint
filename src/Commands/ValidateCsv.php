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
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use Symfony\Component\Console\Input\InputOption;

final class ValidateCsv extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('validate:csv')
            ->setDescription('Validate CSV file by rule')
            ->addOption(
                'csv',
                'c',
                InputOption::VALUE_REQUIRED,
                'CSV filepath to validate. If not set or empty, then the STDIN is used.',
            )
            ->addOption(
                'schema',
                's',
                InputOption::VALUE_REQUIRED,
                'Schema rule filepath',
            );

        parent::configure();
    }

    protected function executeAction(): int
    {
        $csvFilename    = $this->getCsvFilepath();
        $schemaFilename = $this->getSchemaFilepath();

        $csvFile    = new CsvFile($csvFilename, $schemaFilename);
        $errorSuite = $csvFile->validate();
        if ($errorSuite->count() > 0) {
            $this->_($errorSuite->render(ErrorSuite::RENDER_TEXT), OutLvl::ERROR);

            throw new Exception('CSV file is not valid! Found ' . $errorSuite->count() . ' errors.');
        }

        $this->_('<green>Looks good!</green>');

        return self::SUCCESS;
    }

    private function getCsvFilepath(): string
    {
        $csvFilename = $this->getOptString('csv');

        if (\file_exists($csvFilename) === false) {
            throw new Exception("CSV file not found: {$csvFilename}");
        }

        $this->_('<blue>CSV    :</blue> ' . \str_replace(PATH_ROOT, '.', \realpath($csvFilename)));

        return $csvFilename;
    }

    private function getSchemaFilepath(): string
    {
        $schemaFilename = $this->getOptString('schema');

        if (\file_exists($schemaFilename) === false) {
            throw new Exception("Schema file not found: {$schemaFilename}");
        }

        $this->_('<blue>Schema :</blue> ' . \str_replace(PATH_ROOT, '.', \realpath($schemaFilename)));

        return $schemaFilename;
    }
}
