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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboLastNum;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboLastNumTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboLastNum::class;

    public function testEqual(): void
    {
        $rule = $this->create(3.00, Combo::EQ);

        isSame('', $rule->test(['1.5000', '2', '03.000000']));

        isSame(
            'The last value in the column is "4.5", which is not equal than the expected "3"',
            $rule->test(['2.00', '3', '4.5']),
        );
    }
}
