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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboMedian;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

class ComboMedianTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboMedian::class;

    public function testEqual(): void
    {
        $rule = $this->create(5.5, Combo::EQ);

        isSame('', $rule->test(Tools::range(1, 10)));

        isSame(
            'The median in the column is "6", which is not equal than the expected "5.5"',
            $rule->test(Tools::range(1, 11)),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(6, Combo::NOT);

        isSame('', $rule->test(Tools::range(1, 10)));

        isSame(
            'The median in the column is "6", which is equal than the not expected "6"',
            $rule->test(Tools::range(1, 11)),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(1.999, Combo::MIN);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The median in the column is "1.5", which is less than the expected "1.999"',
            $rule->test(['1', '2']),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(2, Combo::MAX);

        isSame('', $rule->test(['1', '2', '3']));

        isSame(
            'The median in the column is "3", which is greater than the expected "2"',
            $rule->test(['1', '2', '3', '4.5', '1000']),
        );
    }

    public function testInvalidParsing(): void
    {
        $rule = $this->create(0, Combo::EQ);
        isSame('', $rule->test([]));
    }
}
