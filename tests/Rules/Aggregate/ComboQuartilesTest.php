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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboQuartiles;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboQuartilesTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboQuartiles::class;

    public function testEqual(): void
    {
        $range = \range(2, 400);

        $rule = $this->create(['exclusive', '0%', 2], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['exclusive', 'Q1', 101], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['exclusive', 'Q2', 201], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['exclusive', 'Q3', 301], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['exclusive', '100%', 400], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['exclusive', 'IQR', 200], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['inclusive', '0%', 2], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['inclusive', 'Q1', 101.5], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['inclusive', 'Q2', 201], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['inclusive', 'Q3', 300.5], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['inclusive', '100%', 400], Combo::EQ);
        isSame('', $rule->test($range));

        $rule = $this->create(['inclusive', 'IQR', 199], Combo::EQ);
        isSame('', $rule->test($range));

        isSame(
            'The quartile in the column is "100", which is not equal than the expected "199"',
            $rule->test(\range(1, 200)),
        );
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create([950.05], Combo::EQ);
        isSame(
            'The rule expects exactly three params: ' .
            'method (exclusive, inclusive), type (0%, Q1, Q2, Q3, 100%, IQR), expected value (float)',
            $rule->test(\range(1, 200)),
        );

        $rule = $this->create(['qwerty', 'IQR', 5], Combo::EQ);
        isSame(
            'Unknown quartile method: "qwerty". Allowed: "exclusive", "inclusive"',
            $rule->test(\range(1, 200)),
        );

        $rule = $this->create(['inclusive', 'QQQQ', 5], Combo::EQ);
        isSame(
            'Unknown quartile type: "QQQQ". Allowed: "0%", "Q1", "Q2", "Q3", "100%", "IQR"',
            $rule->test(\range(1, 200)),
        );
    }
}
