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

use JBZoo\CsvBlueprint\Rules\Cell\IsCapitalize;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsCapitalizeTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsCapitalize::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0'));
        isSame('', $rule->test('False'));
        isSame('', $rule->test('Qwe Rty'));
        isSame('', $rule->test(' Qwe Rty'));
        isSame('', $rule->test(' '));

        $rule = $this->create(false);
        isSame(null, $rule->validate('qwerty'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "qwerty" should be in capitalize',
            $rule->test('qwerty'),
        );
        isSame(
            'Value "qwe Rty" should be in capitalize',
            $rule->test('qwe Rty'),
        );
    }
}
