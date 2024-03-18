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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCoefOfVar;
use JBZoo\PHPUnit\Rules\AbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboCoefOfVarTest extends AbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboCoefOfVar::class;

    public function testEqual(): void
    {
        $rule = $this->create(0.18856180831641, Combo::EQ);
        isSame('', $rule->test([13, 18, 13, 14, 13, 16, 14, 21, 13]));

        $rule = $this->create(3, Combo::EQ);
        isSame(
            'The Coefficient of variation in the column is "0.18856180831641", ' .
            'which is not equal than the expected "3"',
            $rule->test([13, 18, 13, 14, 13, 16, 14, 21, 13]),
        );
    }
}
