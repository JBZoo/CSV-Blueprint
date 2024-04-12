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

namespace JBZoo\PHPUnit\Tools;

use JBZoo\CsvBlueprint\Tools\RandomCsvGenerator;
use JBZoo\PHPUnit\TestCase;

class RandomCsvGeneratorTest extends TestCase
{
    public function testGenerator(): void
    {
        $outputFile = PROJECT_BUILD . '/random_data.csv';
        (new RandomCsvGenerator(
            10,
            $outputFile,
            ['col 1', 'col 2', 'col 3', 'col 4'],
        ))->generateCsv();

        self::assertFileExists($outputFile);

        $content = \file_get_contents($outputFile);
        self::assertStringContainsString("\"col 1\",\"col 2\",\"col 3\",\"col 4\"\n", $content);
    }
}
