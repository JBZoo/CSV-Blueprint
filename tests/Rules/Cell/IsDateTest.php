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

use JBZoo\CsvBlueprint\Rules\Cell\IsDate;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsDateTest extends AbstractCellRule
{
    protected string $ruleClass = IsDate::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('2000-03-28'));
        isSame('', $rule->test('2000-03-28 12:34:56'));
        isSame('', $rule->test('+1 day'));
        isSame('', $rule->test('now'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame(
            'Value "1" is not a valid date.',
            $rule->test('1'),
        );

        $rule = $this->create(true);
        isSame(
            'Value "" is not a valid date.',
            $rule->test(''),
        );

        $rule = $this->create(true);
        isSame(
            'Value "qwerty" is not a valid date.',
            $rule->test('qwerty'),
        );
    }
}
