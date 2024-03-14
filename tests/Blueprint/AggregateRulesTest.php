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

use JBZoo\CsvBlueprint\AggregateRules\Average;
use JBZoo\CsvBlueprint\AggregateRules\AverageMax;
use JBZoo\CsvBlueprint\AggregateRules\AverageMin;
use JBZoo\CsvBlueprint\AggregateRules\IsUnique;
use JBZoo\CsvBlueprint\AggregateRules\Median;
use JBZoo\CsvBlueprint\AggregateRules\Sum;
use JBZoo\CsvBlueprint\AggregateRules\SumMax;
use JBZoo\CsvBlueprint\AggregateRules\SumMin;
use JBZoo\PHPUnit\PHPUnit;

use function JBZoo\PHPUnit\isSame;

final class AggregateRulesTest extends PHPUnit
{
    protected function setUp(): void
    {
        \date_default_timezone_set('UTC');
    }

    public function testUnique(): void
    {
        $rule = new IsUnique('prop', true);
        isSame(null, $rule->validate([1]));
        isSame(null, $rule->validate([]));
        isSame(null, $rule->validate(['1', '2', '3']));
        isSame(
            '"ag:is_unique" at line 1, column "prop". Column has non-unique values. Unique: 3, total: 4.',
            \strip_tags((string)$rule->validate(['1', '2', '3', '3'])),
        );
    }

    public function testSum(): void
    {
        $rule = new Sum('prop', 6);
        isSame(null, $rule->validate(['1', '2', '3']));
        isSame(null, $rule->validate(['1', '2', '3', '', ' ']));
        isSame(null, $rule->validate(['1.5', '2.5', '2']));

        isSame(
            '"ag:sum" at line 1, column "prop". Column sum is not equal to expected. Actual: 0, expected: 6.',
            \strip_tags((string)$rule->validate([])),
        );
        isSame(
            '"ag:sum" at line 1, column "prop". Column sum is not equal to expected. Actual: 11, expected: 6.',
            \strip_tags((string)$rule->validate(['1', '2', '3', '3', '1 ', ' 1'])),
        );
    }

    public function testSumMin(): void
    {
        $rule = new SumMin('prop', 6);
        isSame(null, $rule->validate(['1', '2', '3']));
        isSame(null, $rule->validate(['1.5', '2.5', '2', '', ' ', ' 1']));
        isSame(null, $rule->validate(['1.5', '2.5', '10']));

        isSame(
            '"ag:sum_min" at line 1, column "prop". Column sum is less than expected. Actual: 0, expected: 6.',
            \strip_tags((string)$rule->validate([])),
        );
        isSame(
            '"ag:sum_min" at line 1, column "prop". Column sum is less than expected. Actual: 3, expected: 6.',
            \strip_tags((string)$rule->validate(['1', '2'])),
        );
    }

    public function testSumMax(): void
    {
        $rule = new SumMax('prop', 6);
        isSame(null, $rule->validate([]));
        isSame(null, $rule->validate(['1', '2', '3']));
        isSame(null, $rule->validate(['1.5', '2.5', '2', '', ' ', ' 0']));

        isSame(
            '"ag:sum_max" at line 1, column "prop". Column sum is greater than expected. Actual: 14, expected: 6.',
            \strip_tags((string)$rule->validate(['1.5', '2.5', '10'])),
        );
    }

    public function testAverage(): void
    {
        $rule = new Average('prop', 0);
        isSame(null, $rule->validate([]));

        $rule = new Average('prop', 1);
        isSame(null, $rule->validate(['1']));
        isSame(null, $rule->validate(['1', '1']));
        isSame(null, $rule->validate(['0.0', '2']));

        isSame(
            '"ag:average" at line 1, column "prop". Column average is not equal to expected. Actual: 2, expected: 1.',
            \strip_tags((string)$rule->validate(['1', '2', '3'])),
        );
    }

    public function testAverageMin(): void
    {
        $rule = new AverageMin('prop', 0);
        isSame(null, $rule->validate([]));

        $rule = new AverageMin('prop', 1);
        isSame(null, $rule->validate(['1']));
        isSame(null, $rule->validate(['1', '1']));
        isSame(null, $rule->validate(['0.0', '2']));

        isSame(
            '"ag:average_min" at line 1, column "prop". ' .
            'Column average is less than expected. Actual: -0.5, expected: 1.',
            \strip_tags((string)$rule->validate(['-1', '0'])),
        );
    }

    public function testAverageMax(): void
    {
        $rule = new AverageMax('prop', 0);
        isSame(null, $rule->validate([]));

        $rule = new AverageMax('prop', 1);
        isSame(null, $rule->validate(['1']));
        isSame(null, $rule->validate(['1', '1']));
        isSame(null, $rule->validate(['0.0', '2']));

        isSame(
            '"ag:average_max" at line 1, column "prop". ' .
            'Column average is greater than expected. Actual: 5, expected: 1.',
            \strip_tags((string)$rule->validate(['10', '0'])),
        );
    }

    public function testMedian(): void
    {
        $rule = new Median('prop', 0);
        isSame(null, $rule->validate([]));

        $rule = new Median('prop', 1);
        isSame(null, $rule->validate(['1']));
        isSame(null, $rule->validate(['1', '1']));
        isSame(null, $rule->validate(['0.0', '2']));

        isSame(
            '"ag:median" at line 1, column "prop". ' .
            'Column median is not equal to expected. Actual: 4.5, expected: 1.',
            \strip_tags((string)$rule->validate(['1', '2', '3', '4', '5', '8', '10', '20'])),
        );
    }
}
