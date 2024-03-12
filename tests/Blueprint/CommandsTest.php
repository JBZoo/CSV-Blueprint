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

namespace JBZoo\PHPUnit\Blueprint;

use JBZoo\Cli\CliApplication;
use JBZoo\CsvBlueprint\Commands\ValidateCsv;
use JBZoo\PHPUnit\PHPUnit;
use JBZoo\Utils\Cli;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function JBZoo\Data\yml;
use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;

final class CommandsTest extends PHPUnit
{
    public function testCreateCsvHelp(): void
    {
        isFileContains(\implode("\n", [
            '```',
            './csv-blueprint validate:csv --help',
            '',
            '',
            $this->realExecution('validate:csv', ['help' => null]),
            '```',
        ]), PROJECT_ROOT . '/README.md');
    }

    public function testCreateValidatePositive(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = $this->virtualExecution('validate:csv', [
            'csv'    => "{$rootPath}/tests/fixtures/demo.csv",
            'schema' => "{$rootPath}/tests/schemas/demo_valid.yml",
        ]);

        $expected = \implode("\n", [
            'CSV    : ./tests/fixtures/demo.csv',
            'Schema : ./tests/schemas/demo_valid.yml',
            '',
            'Looks good!',
            '',
        ]);

        isSame(0, $exitCode);
        isSame($expected, $actual);
    }

    public function testCreateValidateNegative(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = $this->virtualExecution('validate:csv', [
            'csv'    => "{$rootPath}/tests/fixtures/demo.csv", // Full path
            'schema' => './tests/schemas/demo_invalid.yml',    // Relative path
        ]);

        $expected = \implode("\n", [
            'CSV    : ./tests/fixtures/demo.csv',
            'Schema : ./tests/schemas/demo_invalid.yml',
            '',
            '+------+------------------+--------------+-- demo.csv --------------------------------------------+',
            '| Line | id:Column        | Rule         | Message                                                |',
            '+------+------------------+--------------+--------------------------------------------------------+',
            '| 5    | 2:Float          | max          | Value "74605.944" is greater than "74605"              |',
            '| 5    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red",   |',
            '|      |                  |              | "green", "Blue"]                                       |',
            '| 6    | 0:Name           | min_length   | Value "Carl" (length: 4) is too short. Min length is 5 |',
            '| 6    | 3:Birthday       | min_date     | Value "1955-05-14" is less than the minimum date       |',
            '|      |                  |              | "1955-05-15T00:00:00.000+00:00"                        |',
            '| 8    | 3:Birthday       | min_date     | Value "1955-05-14" is less than the minimum date       |',
            '|      |                  |              | "1955-05-15T00:00:00.000+00:00"                        |',
            '| 9    | 3:Birthday       | max_date     | Value "2010-07-20" is more than the maximum date       |',
            '|      |                  |              | "2009-01-01T00:00:00.000+00:00"                        |',
            '| 11   | 0:Name           | min_length   | Value "Lois" (length: 4) is too short. Min length is 5 |',
            '+------+------------------+--------------+-- demo.csv --------------------------------------------+',
            '',
            'Found 7 issues in CSV file.',
            '',
        ]);

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    public function testCreateValidateNegativeMultiple(): void
    {
        $rootPath = PROJECT_ROOT;

        $options = [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = $this->virtualExecution('validate:csv', $options);

        \file_put_contents(PROJECT_ROOT . '/build/dump.php', $actual);

        $expected = <<<'TXT'
            CSV    : ./tests/fixtures/batch/sub/demo-3.csv
            CSV    : ./tests/fixtures/batch/demo-2.csv
            CSV    : ./tests/fixtures/batch/demo-1.csv
            Schema : ./tests/schemas/demo_invalid.yml
            
            +------+------------+------------+----- demo-2.csv ---------------------------------------+
            | Line | id:Column  | Rule       | Message                                                |
            +------+------------+------------+--------------------------------------------------------+
            | 2    | 0:Name     | min_length | Value "Carl" (length: 4) is too short. Min length is 5 |
            | 2    | 3:Birthday | min_date   | Value "1955-05-14" is less than the minimum date       |
            |      |            |            | "1955-05-15T00:00:00.000+00:00"                        |
            | 4    | 3:Birthday | min_date   | Value "1955-05-14" is less than the minimum date       |
            |      |            |            | "1955-05-15T00:00:00.000+00:00"                        |
            | 5    | 3:Birthday | max_date   | Value "2010-07-20" is more than the maximum date       |
            |      |            |            | "2009-01-01T00:00:00.000+00:00"                        |
            | 7    | 0:Name     | min_length | Value "Lois" (length: 4) is too short. Min length is 5 |
            +------+------------+------------+----- demo-2.csv ---------------------------------------+
            
            +------+------------------+--------------+ demo-1.csv ------------------------------------------+
            | Line | id:Column        | Rule         | Message                                              |
            +------+------------------+--------------+------------------------------------------------------+
            | 3    | 2:Float          | max          | Value "74605.944" is greater than "74605"            |
            | 3    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", |
            |      |                  |              | "green", "Blue"]                                     |
            +------+------------------+--------------+ demo-1.csv ------------------------------------------+
            
            Found 7 issues in 2 out of 3 CSV files.
            
            TXT;

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);

        isFileContains(\implode("\n", [
            '```',
            "./csv-blueprint validate:csv {$optionsAsString}",
            '',
            '',
            \str_replace($rootPath, '.', $expected),
            '```',
        ]), PROJECT_ROOT . '/README.md');
    }

    public function testCreateValidateNegativeText(): void
    {
        $rootPath = PROJECT_ROOT;

        [$actual, $exitCode] = $this->virtualExecution('validate:csv', [
            'csv'    => './tests/fixtures/demo.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
            'report' => 'text',
        ]);

        $expected = \implode("\n", [
            'CSV    : ./tests/fixtures/demo.csv',
            'Schema : ./tests/schemas/demo_invalid.yml',
            '',
            '"max" at line 5, column "2:Float". Value "74605.944" is greater than "74605".',

            '"allow_values" at line 5, column "4:Favorite color". Value "blue" is not allowed. ' .
            'Allowed values: ["red", "green", "Blue"].',

            '"min_length" at line 6, column "0:Name". Value "Carl" (length: 4) is too short. ' .
            'Min length is 5.',

            '"min_date" at line 6, column "3:Birthday". Value "1955-05-14" is less than the ' .
            'minimum date "1955-05-15T00:00:00.000+00:00".',

            '"min_date" at line 8, column "3:Birthday". Value "1955-05-14" is less than the ' .
            'minimum date "1955-05-15T00:00:00.000+00:00".',

            '"max_date" at line 9, column "3:Birthday". Value "2010-07-20" is more than the ' .
            'maximum date "2009-01-01T00:00:00.000+00:00".',

            '"min_length" at line 11, column "0:Name". Value "Lois" (length: 4) is too short. ' .
            'Min length is 5.',

            '',
            'Found 7 issues in CSV file.',
            '',
        ]);

        isSame(1, $exitCode, $actual);
        isSame($expected, $actual);
    }

    private function virtualExecution(string $action, array $params = []): array
    {
        $params['no-ansi'] = null;

        $application = new CliApplication();
        $application->add(new ValidateCsv());
        $command = $application->find($action);

        $buffer   = new BufferedOutput();
        $args     = new StringInput(Cli::build('', $params));
        $exitCode = $command->run($args, $buffer);

        return [$buffer->fetch(), $exitCode];
    }

    private function realExecution(string $action, array $params = []): string
    {
        $rootDir = PROJECT_ROOT;

        return Cli::exec(
            \implode(' ', [
                Sys::getBinary(),
                "{$rootDir}/csv-blueprint.php --no-ansi",
                $action,
                '2>&1',
            ]),
            $params,
            $rootDir,
            false,
        );
    }
}
