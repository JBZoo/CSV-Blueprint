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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboNthNum;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboNthNumTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboNthNum::class;

    public function testEqual(): void
    {
        $rule = $this->create([2, 30], Combo::EQ);
        isSame('', $rule->test(['1', '30', '3']));
        isSame(
            'The N-th value in the column is "3", which is not equal than the expected "30"',
            $rule->test(['2.00', '3', '4.5']),
        );

        $rule = $this->create([100, 30], Combo::EQ);
        isSame(
            '"ag:nth_num" at line <red>1</red>, column "prop". ' .
            'The column does not have a line 100, so the value cannot be checked.',
            (string)$rule->validate(['2.00', '3', '4.5']),
        );
        $rule = $this->create([], Combo::EQ);
        isSame(
            '"ag:nth_num" at line <red>1</red>, column "prop". ' .
            'The rule expects exactly two arguments: ' .
            'the first is the line number (without header), the second is the expected value.',
            (string)$rule->validate(['2.00', '3', '4.5']),
        );
    }
}
