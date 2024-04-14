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
use JBZoo\CsvBlueprint\Rules\Cell\ComboLength;
use JBZoo\PHPUnit\Rules\TestAbstractCellRuleCombo;

use function JBZoo\PHPUnit\isSame;
use function JBZoo\PHPUnit\success;

class ComboLengthTest extends TestAbstractCellRuleCombo
{
    protected string $ruleClass = ComboLength::class;

    public function testEqual(): void
    {
        $rule = $this->create(6, Combo::EQ);

        isSame('', $rule->test(''));
        isSame('', $rule->test('123456'));
        isSame(
            'The length of the value "12345" is 5, which is not equal than the expected "6"',
            $rule->test('12345'),
        );

        isSame(
            'The length of the value "<c>12345</c>" is 5, which is not equal than the expected "<green>6</green>"',
            $rule->test('12345', true),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(6, Combo::NOT);

        isSame('', $rule->test(''));
        isSame('', $rule->test('12345'));
        isSame(
            'The length of the value "123456" is 6, which is equal than the not expected "6"',
            $rule->test('123456'),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(6, Combo::MIN);

        isSame('', $rule->test(''));
        isSame('', $rule->test('123456'));
        isSame('', $rule->test('1234567'));
        isSame(
            'The length of the value "12345" is 5, which is less than the expected "6"',
            $rule->test('12345'),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(6, Combo::MAX);

        isSame('', $rule->test(''));
        isSame('', $rule->test('123456'));
        isSame('', $rule->test('12345'));
        isSame(
            'The length of the value "1234567" is 7, which is greater than the expected "6"',
            $rule->test('1234567'),
        );
    }

    public function testInvalidOption(): void
    {
        $this->expectException(\JBZoo\CsvBlueprint\Rules\Exception::class);
        $this->expectExceptionMessage(
            'Invalid option "qwerty" for the "length_max" rule. It should be integer.',
        );

        $rule = $this->create('qwerty', Combo::MAX);
        $rule->validate('12345');
    }

    public function testInvalidParsing(): void
    {
        success('No cases for invalid parsing.');
    }
}
