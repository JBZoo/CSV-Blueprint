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

use JBZoo\PHPUnit\PHPUnit;
use JBZoo\PHPUnit\TestTools;
use JBZoo\Utils\Cli;
use Symfony\Component\Console\Input\StringInput;

use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;

final class ReadmeTest extends PHPUnit
{
    public function testCreateCsvHelp(): void
    {
        isFileContains(\implode("\n", [
            '```',
            './csv-blueprint validate:csv --help',
            '',
            '',
            TestTools::realExecution('validate:csv', ['help' => null]),
            '```',
        ]), PROJECT_ROOT . '/README.md');
    }

    public function testTableOutputExample(): void
    {
        $options = [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => './tests/schemas/demo_invalid.yml',
        ];
        $optionsAsString     = new StringInput(Cli::build('', $options));
        [$actual, $exitCode] = TestTools::virtualExecution('validate:csv', $options);

        isSame(1, $exitCode, $actual);

        isFileContains(\implode("\n", [
            '```',
            "./csv-blueprint validate:csv {$optionsAsString}",
            '',
            '',
            $actual,
            '```',
        ]), PROJECT_ROOT . '/README.md');
    }
}
