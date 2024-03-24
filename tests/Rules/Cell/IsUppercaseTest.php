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

use JBZoo\CsvBlueprint\Rules\Cell\IsUppercase;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsUppercaseTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsUppercase::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0'));
        isSame('', $rule->test('FALSE'));
        isSame('', $rule->test('QWE RTY'));
        isSame('', $rule->test(' '));

        $rule = $this->create(false);
        isSame(null, $rule->validate('Qwerty'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "Qwerty" is not uppercase',
            $rule->test('Qwerty'),
        );
        isSame(
            'Value "qwe Rty" is not uppercase',
            $rule->test('qwe Rty'),
        );
    }
}
