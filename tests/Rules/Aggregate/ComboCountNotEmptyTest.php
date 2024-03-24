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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountNotEmpty;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboCountNotEmptyTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboCountNotEmpty::class;

    public function testEqual(): void
    {
        $rule = $this->create(1, Combo::EQ);

        isSame('', $rule->test(['', '', ' ']));
        isSame('', $rule->test(['', '', '', '1']));

        isSame(
            'The number of not empty rows in the column is "2", which is not equal than the expected "1"',
            $rule->test(['', '', ' ', '1']),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(1, Combo::NOT);

        isSame('', $rule->test(['', '']));

        isSame(
            'The number of not empty rows in the column is "1", which is equal than the not expected "1"',
            $rule->test(['', '1']),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(1, Combo::MIN);

        isSame('', $rule->test(['', '', '', '1']));

        isSame(
            'The number of not empty rows in the column is "0", which is less than the expected "1"',
            $rule->test(['']),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(3, Combo::MAX);

        isSame('', $rule->test(['', '', '']));
        isSame('', $rule->test(['', '']));
        isSame('', $rule->test([]));

        isSame(
            'The number of not empty rows in the column is "5", which is greater than the expected "3"',
            $rule->test([1, 2, 3, 4, 5]),
        );
    }

    public function testInvalidParsing(): void
    {
        $rule = $this->create(0, Combo::EQ);
        isSame('', $rule->test([]));
    }
}
