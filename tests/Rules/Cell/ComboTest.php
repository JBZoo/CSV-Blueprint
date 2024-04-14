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

use JBZoo\CsvBlueprint\Rules\AbstractRule as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboNum;
use JBZoo\PHPUnit\Rules\TestAbstractCellRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboTest extends TestAbstractCellRuleCombo
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

    public function testMin(): void
    {
        $rule = $this->create(6, Combo::MIN);

        isSame('', $rule->test(''));
        isSame('', $rule->test('6'));
        isSame('', $rule->test('7'));
        isSame(
            'The value "5" is less than the expected "6"',
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
            'The value "8" is greater than the expected "6"',
            $rule->test('8'),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(6, Combo::NOT);

        isSame('', $rule->test(''));
        isSame('', $rule->test('5'));
        isSame(
            'The value "6" is equal than the not expected "6"',
            $rule->test('6'),
        );
    }

    public function testLess(): void
    {
        $rule = $this->create(6, Combo::LESS);

        isSame('', $rule->test(''));
        isSame('', $rule->test('5'));
        isSame('', $rule->test('4'));
        isSame(
            'The value "6" is greater and not equal than the expected "6"',
            $rule->test('6'),
        );
        isSame(
            'The value "7" is greater and not equal than the expected "6"',
            $rule->test('7'),
        );
    }

    public function testGreater(): void
    {
        $rule = $this->create(6, Combo::GREATER);

        isSame('', $rule->test(''));
        isSame('', $rule->test('7'));
        isSame(
            'The value "6" is less and not equal than the expected "6"',
            $rule->test('6'),
        );
        isSame(
            'The value "5" is less and not equal than the expected "6"',
            $rule->test('5'),
        );
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create('sdfsd.234234.sdfsd', Combo::NOT);
        isSame('', $rule->test('5'));
    }

    public function testInvalidParsing(): void
    {
        $rule = $this->create(6, Combo::NOT);
        isSame('', $rule->test('qwerty'));
    }

    public function testInvalidOption2(): void
    {
        $this->expectExceptionMessage(
            'Invalid option ["1", "2", "3"] for the "num_not" rule. It should be int/float/string.',
        );

        $rule = $this->create([1, 2, 3], Combo::NOT);
        $rule->validate('true');
    }
}
