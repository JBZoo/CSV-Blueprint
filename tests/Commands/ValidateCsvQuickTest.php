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

namespace JBZoo\PHPUnit\Commands;

use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isContain;
use function JBZoo\PHPUnit\isNotContain;
use function JBZoo\PHPUnit\isNotSame;
use function JBZoo\PHPUnit\isSame;

final class ValidateCsvQuickTest extends TestCase
{
    public function testEnabled(): void
    {
        $expectedFull = <<<'TXT'
            Found Schemas   : 1
            Found CSV files : 3
            Pairs by pattern: 3
            Quick mode enabled!
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) Issues: 1
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            
            
            CSV file validation: 3
            (1/3) Schema: ./tests/schemas/demo_invalid.yml
            (1/3) CSV   : ./tests/fixtures/batch/demo-1.csv
            (1/3) Issues: 1
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            
            (2/3) Schema: ./tests/schemas/demo_invalid.yml
            (2/3) CSV   : ./tests/fixtures/batch/demo-2.csv
            (2/3) Skipped (Quick mode)
            (3/3) Schema: ./tests/schemas/demo_invalid.yml
            (3/3) CSV   : ./tests/fixtures/batch/sub/demo-3.csv
            (3/3) Skipped (Quick mode)
            
            Summary:
              3 pairs (schema to csv) were found based on `filename_pattern`.
              Found 1 issues in 1 schemas.
              Found 1 issues in 1 out of 3 CSV files.
            
            
            TXT;

        isSame($expectedFull, self::getQuickOutput(['Q' => null]));
    }

    public function testDisabled(): void
    {
        $expectedFull = <<<'TXT'
            Found Schemas   : 1
            Found CSV files : 3
            Pairs by pattern: 3
            
            Check schema syntax: 1
            (1/1) Schema: ./tests/schemas/demo_invalid.yml
            (1/1) Issues: 2
            "is_float", column "2:Float". Value "Qwerty" is not a float number.
            "allow_values", column "4:Favorite color". Value "123" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            
            CSV file validation: 3
            (1/3) Schema: ./tests/schemas/demo_invalid.yml
            (1/3) CSV   : ./tests/fixtures/batch/demo-1.csv
            (1/3) Issues: 3
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            "ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 1, total: 2.
            "allow_values" at line 3, column "4:Favorite color". Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"].
            
            (2/3) Schema: ./tests/schemas/demo_invalid.yml
            (2/3) CSV   : ./tests/fixtures/batch/demo-2.csv
            (2/3) Issues: 6
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            "length_min" at line 2, column "0:Name". The length of the value "Carl" is 4, which is less than the expected "5".
            "length_min" at line 7, column "0:Name". The length of the value "Lois" is 4, which is less than the expected "5".
            "date_min" at line 2, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_min" at line 4, column "3:Birthday". The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the expected "1955-05-15 00:00:00 +00:00 (1955-05-15)".
            "date_max" at line 5, column "3:Birthday". The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)".
            
            (3/3) Schema: ./tests/schemas/demo_invalid.yml
            (3/3) CSV   : ./tests/fixtures/batch/sub/demo-3.csv
            (3/3) Issues: 1
            "csv.header" at line 1. Columns not found in CSV: "wrong_column_name".
            
            
            Summary:
              3 pairs (schema to csv) were found based on `filename_pattern`.
              Found 2 issues in 1 schemas.
              Found 10 issues in 3 out of 3 CSV files.
            
            
            TXT;

        isSame($expectedFull, self::getQuickOutput()); // By default is disabled
    }

    public function testOptionShortcuts(): void
    {
        // Disabled
        $disabled1 = self::getQuickOutput();
        $disabled2 = self::getQuickOutput(['quick' => 'no']);
        $disabled3 = self::getQuickOutput(['quick' => '0']);
        isSame($disabled1, $disabled2);
        isSame($disabled1, $disabled3);

        // Enabled
        $enabled1 = self::getQuickOutput(['Q' => null]);
        $enabled2 = self::getQuickOutput(['quick' => null]);
        $enabled3 = self::getQuickOutput(['quick' => 'yes']);
        $enabled4 = self::getQuickOutput(['quick' => '1']);
        isSame($enabled1, $enabled2);
        isSame($enabled1, $enabled3);
        isSame($enabled1, $enabled4);

        isNotSame($enabled1, $disabled1);
        isContain('Quick mode enabled!', $enabled1, false, $enabled1);
        isContain('Skipped (Quick mode)', $enabled1, false, $enabled1);
        isNotContain('Quick mode enabled!', $disabled1, false, $disabled1);
        isNotContain('Skipped (Quick mode)', $disabled1, false, $disabled1);
    }

    private function getQuickOutput(?array $quickOption = null): string
    {
        $options = [
            'csv'    => './tests/fixtures/batch/*.csv',
            'schema' => Tools::DEMO_YML_INVALID,
            'report' => 'text',
        ];

        if ($quickOption !== null) {
            $options = \array_merge($options, $quickOption);
        }

        [$actual, $exitCode] = Tools::virtualExecution('validate:csv', $options);

        isSame(1, $exitCode, $actual);

        return $actual;
    }
}
