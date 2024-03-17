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

use JBZoo\CsvBlueprint\Rules\Cell\IsFloat;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsFloatTest extends AbstractCellRule
{
    protected string $ruleClass = IsFloat::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('1'));
        isSame('', $rule->test('01'));
        isSame('', $rule->test('1.0'));
        isSame('', $rule->test('01.0'));
        isSame('', $rule->test('-1'));
        isSame('', $rule->test('-1.0'));

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "1.000.000" is not a float number',
            $rule->test('1.000.000'),
        );
        isSame(
            'Value " 1" is not a float number',
            $rule->test(' 1'),
        );
    }
}
