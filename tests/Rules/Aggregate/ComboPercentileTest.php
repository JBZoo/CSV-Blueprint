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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboPercentile;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

class ComboPercentileTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboPercentile::class;

    public function testEqual(): void
    {
        $rule = $this->create([95, 950.05], Combo::EQ);
        isSame('', $rule->test(Tools::range(1, 1000)));
        isSame(
            'The percentile in the column is "190.05", which is not equal than the expected "950.05"',
            $rule->test(Tools::range(1, 200)),
        );
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create([950.05], Combo::EQ);
        isSame(
            'The rule expects exactly two params: ' .
            'the first is percentile (P is beet 0.0 and 100.0), the second is the expected value (float)',
            $rule->test(Tools::range(1, 200)),
        );
    }
}
