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
use JBZoo\CsvBlueprint\Rules\Cell\ComboDate;
use JBZoo\PHPUnit\Rules\AbstractCellRuleComboTest;

use function JBZoo\PHPUnit\isSame;

class ComboDateTest extends AbstractCellRuleComboTest
{
    protected string $ruleClass = ComboDate::class;

    public function testEqual(): void
    {
        $rule = $this->create('2000-10-02', Combo::EQ);

        isSame('', $rule->test(''));
        isSame('', $rule->test('2000-10-02'));
        isSame('', $rule->test('2000-10-02 00:00:00'));

        isSame(
            'The date of the value "2000-10-02 00:00:01" is parsed as "2000-10-02 00:00:01 +00:00", ' .
            'which is not equal than the expected "2000-10-02 00:00:00 +00:00 (2000-10-02)"',
            $rule->test('2000-10-02 00:00:01'),
        );

        isSame(
            'The date of the value "<c>2000-10-02 00:00:01</c>" is parsed as "2000-10-02 00:00:01 +00:00", ' .
            'which is not equal than the expected "<green>2000-10-02 00:00:00 +00:00 (2000-10-02)</green>"',
            $rule->test('2000-10-02 00:00:01', true),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create('2000-01-10', Combo::NOT);

        isSame('', $rule->test('2000-01-11'));
        isSame(
            'The date of the value "2000-01-10" is parsed as "2000-01-10 00:00:00 +00:00", ' .
            'which is equal than the not expected "2000-01-10 00:00:00 +00:00 (2000-01-10)"',
            $rule->test('2000-01-10'),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create('2000-01-10', Combo::MIN);
        isSame('', $rule->test(''));
        isSame('', $rule->test('2000-01-10'));
        isSame(
            'The date of the value "2000-01-09" is parsed as "2000-01-09 00:00:00 +00:00", ' .
            'which is less than the expected "2000-01-10 00:00:00 +00:00 (2000-01-10)"',
            $rule->test('2000-01-09'),
        );

        $rule = $this->create('2000-01-10 00:00:00 +01:00', Combo::MIN);
        isSame('', $rule->test('2000-01-10 00:00:00 +01:00'));
        isSame(
            'The date of the value "2000-01-09 23:59:59 Europe/Berlin" is parsed as "2000-01-09 23:59:59 +01:00", ' .
            'which is less than the expected "2000-01-10 00:00:00 +01:00 (2000-01-10 00:00:00 +01:00)"',
            $rule->test('2000-01-09 23:59:59 Europe/Berlin'),
        );

        $rule = $this->create('-1000 years', Combo::MIN);
        isSame('', $rule->test('2000-01-10 00:00:00 +01:00'));
    }

    public function testMax(): void
    {
        $rule = $this->create('2000-01-10', Combo::MAX);
        isSame('', $rule->test(''));
        isSame('', $rule->test('2000-01-09'));
        isSame(
            'The date of the value "2000-01-11" is parsed as "2000-01-11 00:00:00 +00:00", ' .
            'which is greater than the expected "2000-01-10 00:00:00 +00:00 (2000-01-10)"',
            $rule->test('2000-01-11'),
        );

        $rule = $this->create('2000-01-10 00:00:00', Combo::MAX);
        isSame('', $rule->test('2000-01-10 00:00:00'));
        isSame(
            'The date of the value "2000-01-10 00:00:01" is parsed as "2000-01-10 00:00:01 +00:00", ' .
            'which is greater than the expected "2000-01-10 00:00:00 +00:00 (2000-01-10 00:00:00)"',
            $rule->test('2000-01-10 00:00:01'),
        );

        $rule = $this->create('+1 day', Combo::MAX);
        isSame('', $rule->test('now'));
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create('invalid', Combo::MAX);
        isSame(
            'The date of the value "2000-01-10" is parsed as "2000-01-10 00:00:00 +00:00", ' .
            'which is greater than the expected "Can\'t parse date: invalid"',
            $rule->test('2000-01-10'),
        );
    }

    public function testInvalidParsing(): void
    {
        $rule = $this->create('2000-01-10', Combo::MIN);
        isSame(
            'The date of the value "invalid" is parsed as "Can\'t parse date: invalid", ' .
            'which is less than the expected "2000-01-10 00:00:00 +00:00 (2000-01-10)"',
            $rule->test('invalid'),
        );
    }
}
