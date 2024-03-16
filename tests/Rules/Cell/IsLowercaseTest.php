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

use JBZoo\CsvBlueprint\Rules\Cell\IsLowercase;
use JBZoo\PHPUnit\Rules\AbstractCellRuleTest;

use function JBZoo\PHPUnit\isSame;

final class IsLowercaseTest extends AbstractCellRuleTest
{
    protected string $ruleClass = IsLowercase::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0'));
        isSame('', $rule->test('false'));
        isSame('', $rule->test('qwe rty'));
        isSame('', $rule->test(' qwe rty'));
        isSame('', $rule->test(' '));

        $rule = $this->create(false);
        isSame(null, $rule->validate('Qwerty'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "Qwerty" should be in lowercase',
            $rule->test('Qwerty'),
        );
        isSame(
            'Value "qwe Rty" should be in lowercase',
            $rule->test('qwe Rty'),
        );
    }
}
