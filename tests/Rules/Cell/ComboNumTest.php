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

use JBZoo\CsvBlueprint\Rules\AbstractCombo as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboNum;
use JBZoo\PHPUnit\Rules\AbstractCellRuleComboTest;

use function JBZoo\PHPUnit\isSame;

class ComboNumTest extends AbstractCellRuleComboTest
{
    protected string $ruleClass = ComboNum::class;

    public function testEqual(): void
    {
        $rule = $this->create(6, Combo::EQ);

        isSame('', $rule->test(''));
        isSame('', $rule->test('6'));
        isSame(
            'The number of the value "12345", which is not equal than the expected "6"',
            $rule->test('12345'),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(6, Combo::MIN);

        isSame('', $rule->test(''));
        isSame('', $rule->test('6'));
        isSame('', $rule->test('7'));
        isSame(
            'The number of the value "5", which is less than the expected "6"',
            $rule->test('5'),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(6, Combo::MAX);

        isSame('', $rule->test(''));
        isSame('', $rule->test('6'));
        isSame('', $rule->test('5'));
        isSame(
            'The number of the value "8", which is greater than the expected "6"',
            $rule->test('8'),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(6, Combo::NOT);

        isSame('', $rule->test(''));
        isSame('', $rule->test('5'));
        isSame(
            'The number of the value "6", which is equal than the not expected "6"',
            $rule->test('6'),
        );
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create('sdfsd.234234.sdfsd', Combo::NOT);
        isSame('', $rule->test('5'));
    }

    public function testInvalidParsing(): void
    {
        // TODO: This test is not working as expected. It should throw an exception.
        $rule = $this->create(6, Combo::NOT);
        isSame('', $rule->test('qwerty'));
    }
}
