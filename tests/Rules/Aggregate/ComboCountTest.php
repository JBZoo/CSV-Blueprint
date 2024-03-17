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

use JBZoo\CsvBlueprint\Rules\AbstarctRule as Combo;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCount;
use JBZoo\PHPUnit\Rules\AbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboCountTest extends AbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboCount::class;

    public function testEqual(): void
    {
        $rule = $this->create(3, Combo::EQ);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The number of rows in the column is "4", which is not equal than the expected "3"',
            $rule->test(['1', '2', '3', '4.5']),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(3, Combo::NOT);

        isSame('', $rule->test(['1', '2', '3', '4.5']));

        isSame(
            'The number of rows in the column is "3", which is equal than the not expected "3"',
            $rule->test(['1', '2', '3']),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(3, Combo::MIN);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The number of rows in the column is "2", which is less than the expected "3"',
            $rule->test(['1', '2']),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(3, Combo::MAX);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The number of rows in the column is "4", which is greater than the expected "3"',
            $rule->test(['1', '2', '3', '4.5']),
        );
    }

    public function testInvalidOption(): void
    {
        $this->expectExceptionMessage(
            'Invalid option "[1, 2]" for the "ag:count_max" rule. It should be integer/float.',
        );
        $rule = $this->create([1, 2], Combo::MAX);
        $rule->validate(['1', '2', '3']);
    }

    public function testInvalidParsing(): void
    {
        $rule = $this->create(0, Combo::EQ);
        isSame('', $rule->test([]));
    }
}
