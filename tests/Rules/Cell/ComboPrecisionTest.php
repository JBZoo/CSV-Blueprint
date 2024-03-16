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
use JBZoo\CsvBlueprint\Rules\Cell\ComboPrecision;
use JBZoo\PHPUnit\Rules\AbstractCellRuleComboTest;

use function JBZoo\PHPUnit\isSame;

class ComboPrecisionTest extends AbstractCellRuleComboTest
{
    protected string $ruleClass = ComboPrecision::class;

    public function testEqual(): void
    {
        $rule = $this->create(3, Combo::EQ);

        isSame('', $rule->test(''));
        isSame('', $rule->test('0.123'));
        isSame('', $rule->test('0.120'));
        isSame(
            'The precision of the value "0.12" is 2, which is not equal than the expected "3"',
            $rule->test('0.12'),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(3, Combo::MIN);

        isSame('', $rule->test(''));
        isSame('', $rule->test('0.123'));
        isSame('', $rule->test('0.12345'));
        isSame(
            'The precision of the value "0.12" is 2, which is less than the expected "3"',
            $rule->test('0.12'),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(3, Combo::MAX);

        isSame('', $rule->test(''));
        isSame('', $rule->test('0.123'));
        isSame('', $rule->test('0.12'));
        isSame('', $rule->test('0.1'));
        isSame(
            'The precision of the value "0.12345" is 5, which is greater than the expected "3"',
            $rule->test('0.12345'),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(3, Combo::NOT);

        isSame('', $rule->test(''));
        isSame('', $rule->test('0.12'));
        isSame('', $rule->test('0.1245'));
        isSame(
            'The precision of the value "0.123" is 3, which is equal than the not expected "3"',
            $rule->test('0.123'),
        );
    }

    public function testInvalidOption(): void
    {
        $this->expectExceptionMessage('Invalid option "s.223" for the "precision_not" rule. It should be integer.');
        $rule = $this->create('s.223', Combo::NOT);
        isSame('', $rule->test('5'));
    }

    public function testInvalidParsing(): void
    {
        // TODO: This test is not working as expected. It should throw an exception.
        $rule = $this->create(3, Combo::NOT);
        isSame('', $rule->test('qwerty'));
    }
}
