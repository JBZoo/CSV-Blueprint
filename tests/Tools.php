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

namespace JBZoo\PHPUnit;

use JBZoo\Cli\CliApplication;
use JBZoo\CsvBlueprint\Commands\ValidateCsv;
use JBZoo\Utils\Cli;
use JBZoo\Utils\Sys;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class Tools
{
    public static function virtualExecution(string $action, array $params = []): array
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

    public static function realExecution(string $action, array $params = [], string $extra = '--no-ansi'): string
    {
        $rootDir = PROJECT_ROOT;

        return Cli::exec(
            \implode(' ', [
                Sys::getBinary(),
                "{$rootDir}/csv-blueprint.php {$extra}",
                $action,
                '2>&1',
            ]),
            $params,
            $rootDir,
            false,
        );
    }

    public static function dumpText($text): void
    {
        \file_put_contents(PROJECT_ROOT . '/build/dump.txt', $text);
    }

    public static function getRule(?string $columnName, ?string $ruleName, array|bool|float|int|string $options): array
    {
        return ['columns' => [['name' => $columnName, 'rules' => [$ruleName => $options]]]];
    }

    public static function getAggregateRule(
        ?string $columnName,
        ?string $ruleName,
        array|bool|float|int|string $options,
    ): array {
        return ['columns' => [['name' => $columnName, 'aggregate_rules' => [$ruleName => $options]]]];
    }
}
