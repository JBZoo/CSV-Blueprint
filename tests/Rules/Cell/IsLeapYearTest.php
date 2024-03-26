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

use JBZoo\CsvBlueprint\Rules\Cell\IsLeapYear;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsLeapYearTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsLeapYear::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);

        isSame('', $rule->test(''));
        isSame('', $rule->test('2008'));
        isSame('', $rule->test('2008-02-29'));
        isSame('', $rule->test('2008-02-29 00:00:00'));
        isSame('', $rule->test('2008-02-29 23:59:59 UTC'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('90.1.1.1.1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Cell value "1230" should be a leap year',
            $rule->test('1230'),
        );
        isSame(
            'Cell value "2009" should be a leap year',
            $rule->test('2009'),
        );
        isSame(
            'Cell value "90.1.1.1.1" should be a leap year',
            $rule->test('90.1.1.1.1'),
        );
    }
}
