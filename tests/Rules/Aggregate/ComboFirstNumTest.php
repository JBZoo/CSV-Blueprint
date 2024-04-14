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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboFirstNum;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboFirstNumTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboFirstNum::class;

    public function testEqual(): void
    {
        $rule = $this->create(1.50, Combo::EQ);

        isSame('', $rule->test(['1.5000', '2', '3']));

        isSame(
            'The first value in the column is "2", which is not equal than the expected "1.5"',
            $rule->test(['2.00', '3', '4.5']),
        );
    }
}
