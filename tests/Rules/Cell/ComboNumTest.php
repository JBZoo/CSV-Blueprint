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

namespace JBZoo\PHPUnit\Rules\Cell;

use JBZoo\CsvBlueprint\Rules\AbstarctRule as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboNum;
use JBZoo\PHPUnit\Rules\TestAbstractCellRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboNumTest extends TestAbstractCellRuleCombo
{
    protected string $ruleClass = ComboNum::class;

    public function testEqual(): void
    {
        $rule = $this->create(6, Combo::EQ);

        isSame('', $rule->test(''));
        isSame('', $rule->test('6'));
        isSame(
            'The value "12345" is not equal than the expected "6"',
            $rule->test('12345'),
        );

        $rule = $this->create(1.2e3, Combo::EQ);
        isSame('', $rule->test('1.2e3'));
    }
}
