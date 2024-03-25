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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboRootMeanSquare;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboRootMeanSquareTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboRootMeanSquare::class;

    public function testEqual(): void
    {
        $rule = $this->create(15.235193176035, Combo::EQ);
        isSame('', $rule->test([]));
        isSame('', $rule->test([13, 18, 13, 14, 13, 16, 14, 21, 13]));

        $rule = $this->create(1, Combo::EQ);
        isSame(
            'The root mean square (quadratic mean) in the column is "15.235193176035", ' .
            'which is not equal than the expected "1"',
            $rule->test([13, 18, 13, 14, 13, 16, 14, 21, 13]),
        );
    }
}
