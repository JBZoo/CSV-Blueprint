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

use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;

final class CommandsTest extends PHPUnit
{
    public function testCreateCsvHelp(): void
    {
        $rootPath = PROJECT_ROOT;

        $actual = $this->realExecution('validate:csv', ['help' => null]);

        $expected = \implode("\n", [
            'Description:',
            '  Validate CSV file by schema.',
            '',
            'Usage:',
            '  validate:csv [options]',
            '',
            'Options:',
            '  -c, --csv=CSV                  CSV filepath to validate.',
            '  -s, --schema=SCHEMA            Schema rule filepath. It can be a .yml/.json/.php file.',
            '  -o, --output=OUTPUT            Report output format. Available options: text, table, github, ' .
            'gitlab, teamcity, junit [default: "table"]',
            '      --no-progress              Disable progress bar animation for logs. It will be used only ' .
            'for text output format.',
            '      --mute-errors              Mute any sort of errors. So exit code will be always "0" ' .
            '(if it\'s possible).',
            '                                 It has major priority then --non-zero-on-error. It\'s on your own risk!',
            '      --stdout-only              For any errors messages application will use StdOut instead of StdErr. ' .
            'It\'s on your own risk!',
            '      --non-zero-on-error        None-zero exit code on any StdErr message.',
            '      --timestamp                Show timestamp at the beginning of each message.It will be used only ' .
            'for text output format.',
            '      --profile                  Display timing and memory usage information.',
            '      --output-mode=OUTPUT-MODE  Output format. Available options:',
            '                                 text - Default text output format, userfriendly and easy to read.',
            '                                 cron - Shortcut for crontab. It\'s basically focused on human-readable ' .
            'logs output.',
            '                                 It\'s combination of --timestamp --profile --stdout-only --no-progress ' .
            '-vv.',
            '                                 logstash - Logstash output format, for integration with ELK stack.',
            '                                  [default: "text"]',
            '      --cron                     Alias for --output-mode=cron. Deprecated!',
            '  -h, --help                     Display help for the given command. When no command is given display ' .
            'help for the list command',
            '  -q, --quiet                    Do not output any message',
            '  -V, --version                  Display this application version',
            '      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output',
            '  -n, --no-interaction           Do not ask any interactive question',
            '  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more ' .
            'verbose output and 3 for debug',
            '',
        ]);

        isSame($expected, $actual);
        isFileContains(\implode("\n", [
            '```',
            './csv-blueprint validate:csv --help',
            '',
            '',
            $expected,
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
            "CSV    : {$rootPath}/tests/fixtures/demo.csv",
            "Schema : {$rootPath}/tests/schemas/demo_valid.yml",
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
            'csv'    => "{$rootPath}/tests/fixtures/demo.csv",
            'schema' => './tests/schemas/demo_invalid.yml',
        ]);

        $expected = \implode("\n", [
            "CSV    : {$rootPath}/tests/fixtures/demo.csv",
            "Schema : {$rootPath}/tests/schemas/demo_invalid.yml",
            '',
            '+------+------------------+--------------+-- demo.csv --------------------------------------------+',
            '| Line | id:Column        | Rule         | Message                                                |',
            '+------+------------------+--------------+--------------------------------------------------------+',
            '| 1    | 1:               | csv.header   | Property "name" is not defined in schema:              |',
            '|      |                  |              | "./tests/schemas/demo_invalid.yml"                     |',
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
            'CSV file is not valid! Found 8 errors.',
            '',
        ]);

        isSame(1, $exitCode);
        isSame($expected, $actual);

        isFileContains(\implode("\n", [
            '```',
            './csv-blueprint validate:csv --output=table --csv=./tests/fixtures/demo.csv --schema=./tests/schemas/demo_invalid.yml',
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
            'csv'    => "{$rootPath}/tests/fixtures/demo.csv",
            'schema' => "{$rootPath}/tests/schemas/demo_invalid.yml",
            'output' => 'text',
        ]);

        $expected = \implode("\n", [
            "CSV    : {$rootPath}/tests/fixtures/demo.csv",
            "Schema : {$rootPath}/tests/schemas/demo_invalid.yml",
            '',
            '"csv.header" at line 1, column "1:". Property "name" is not defined in schema: ' .
            "\"{$rootPath}/tests/schemas/demo_invalid.yml\".",

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
            'CSV file is not valid! Found 8 errors.',
            '',
        ]);

        isSame(1, $exitCode);
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
