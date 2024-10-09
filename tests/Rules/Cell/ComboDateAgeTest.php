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
use JBZoo\CsvBlueprint\Rules\Cell\ComboDateAge;
use JBZoo\PHPUnit\Rules\TestAbstractCellRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboDateAgeTest extends TestAbstractCellRuleCombo
{
    protected string $ruleClass = ComboDateAge::class;

    public function testEqual(): void
    {
        $rule = $this->create(0, Combo::EQ);

        isSame('', $rule->test(''));
        isSame('', $rule->test('now'));

        isSame(
            'The age of the value "2020-10-02" is parsed as "4" years, ' .
            'which is not equal than the expected "0 years"',
            $rule->test('2020-10-02'),
        );

        isSame(
            'The age of the value "<c>qwerty</c>" is ' .
            '<red>Failed to parse time string (qwerty) at position 0 (q): ' .
            'The timezone could not be found in the database</red>, ' .
            'which is not equal than the expected "<green>0 years</green>"',
            $rule->test('qwerty', true),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(21, Combo::MIN);

        isSame('', $rule->test(''));
        isSame('', $rule->test('+22 years'));
        isSame('', $rule->test('2100-01-01'));
        isSame('', $rule->test('2100-01'));

        isSame(
            'The age of the value "2020-10-02" is parsed as "4" years, ' .
            'which is less than the expected "21 years"',
            $rule->test('2020-10-02'),
        );

        isSame(
            'The age of the value "<c>qwerty</c>" is ' .
            '<red>Failed to parse time string (qwerty) at position 0 (q): ' .
            'The timezone could not be found in the database</red>, ' .
            'which is less than the expected "<green>21 years</green>"',
            $rule->test('qwerty', true),
        );
    }
}
