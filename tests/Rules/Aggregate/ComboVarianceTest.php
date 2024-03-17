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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboPopulationVariance;
use JBZoo\PHPUnit\Rules\AbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboVarianceTest extends AbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboPopulationVariance::class;

    public function testEqual(): void
    {
        $rule = $this->create(10, Combo::EQ);

        isSame(
            'The population variance in the column is "9.3333333333333", which is not equal than the expected "10"',
            $rule->test([13, 18, 13, 14, 13, 16, 14, 21, 10]),
        );

        isSame(
            'The population variance in the column is "0.1875", which is not equal than the expected "10"',
            $rule->test(['1', '2', '1', '1']),
        );
    }
}
