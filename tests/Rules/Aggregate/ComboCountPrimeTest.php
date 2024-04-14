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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountPrime;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

class ComboCountPrimeTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboCountPrime::class;

    public function testEqual(): void
    {
        $rule = $this->create(25, Combo::EQ);

        isSame('', $rule->test([]));
        isSame('', $rule->test(Tools::range(1, 100)));

        isSame(
            'The number of prime values in the column is "4", which is not equal than the expected "25"',
            $rule->test(Tools::range(1, 10)),
        );
    }
}
