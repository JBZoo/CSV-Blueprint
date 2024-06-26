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

use JBZoo\CsvBlueprint\Rules\Cell\NotAllowValues;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class NotAllowValuesTest extends TestAbstractCellRule
{
    protected string $ruleClass = NotAllowValues::class;

    public function testPositive(): void
    {
        $rule = $this->create(['1', '2', '3']);
        isSame('', $rule->test(''));
        isSame('', $rule->test(' '));
        isSame('', $rule->test('4'));
        isSame('', $rule->test('1qwerty'));

        $rule = $this->create([' ']);
        isSame('', $rule->test(''));
    }

    public function testNegative(): void
    {
        $rule = $this->create(['invalid', ' ']);
        isSame('Value "invalid" is not allowed', $rule->test('invalid'));
        isSame('Value " " is not allowed', $rule->test(' '));

        $rule = $this->create([]);
        isSame('Not allowed values are not defined', $rule->test('invalid'));
    }
}
