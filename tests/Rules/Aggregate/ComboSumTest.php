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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboSum;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboSumTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboSum::class;

    public function testEqual(): void
    {
        $rule = $this->create(6, Combo::EQ);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The sum of numbers in the column is "10.5", which is not equal than the expected "6"',
            $rule->test(['1', '2', '3', '4.5']),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(6, Combo::NOT);

        isSame('', $rule->test(['1', '2', '3', '4.5']));

        isSame(
            'The sum of numbers in the column is "6", which is equal than the not expected "6"',
            $rule->test(['1', '2', '3']),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(6, Combo::MIN);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The sum of numbers in the column is "3", which is less or equal than the expected "6"',
            $rule->test(['1', '2']),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(6, Combo::MAX);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The sum of numbers in the column is "10.5", which is greater or equal than the expected "6"',
            $rule->test(['1', '2', '3', '4.5']),
        );
    }

    public function testInvalidParsing(): void
    {
        $this->expectExceptionMessage('The value should be an array of numbers/strings');
        $rule = $this->create(6, Combo::EQ);
        $rule->validate('6');
    }
}
