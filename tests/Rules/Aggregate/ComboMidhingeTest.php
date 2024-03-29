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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboMidhinge;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

class ComboMidhingeTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboMidhinge::class;

    public function testEqual(): void
    {
        $rule = $this->create(18, Combo::EQ);
        isSame('', $rule->test([]));
        isSame('', $rule->test(Tools::range(1, 35)));

        $rule = $this->create(3, Combo::EQ);
        isSame(
            'The midhinge in the column is "18", which is not equal than the expected "3"',
            $rule->test(Tools::range(1, 35)),
        );
    }
}
