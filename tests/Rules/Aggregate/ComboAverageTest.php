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

namespace JBZoo\PHPUnit\Rules\Aggregate;

use JBZoo\CsvBlueprint\Rules\AbstractRule as Combo;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboAverage;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboAverageTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboAverage::class;

    public function testEqual(): void
    {
        $rule = $this->create(2, Combo::EQ);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The average in the column is "2.625", which is not equal than the expected "2"',
            $rule->test(['1', '2', '3', '4.5']),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(2, Combo::NOT);

        isSame('', $rule->test(['1', '2', '3', '4.5']));

        isSame(
            'The average in the column is "2", which is equal than the not expected "2"',
            $rule->test(['1', '2', '3']),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(1.999, Combo::MIN);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The average in the column is "1.5", which is less than the expected "1.999"',
            $rule->test(['1', '2']),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(2, Combo::MAX);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The average in the column is "2.625", which is greater than the expected "2"',
            $rule->test(['1', '2', '3', '4.5']),
        );
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create([1, 2], Combo::MAX);
        isSame(
            '"ag:average_max" at line <red>1</red>, column "prop". ' .
            '<red>Invalid option ["1", "2"] for the "ag:average_max" rule. It should be integer/float</red>.',
            (string)$rule->validate(['1', '2', '3']),
        );
    }

    public function testInvalidParsing(): void
    {
        $rule = $this->create(0, Combo::EQ);
        isSame('', $rule->test([]));
    }
}
