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

use JBZoo\CsvBlueprint\Rules\Cell\IsTimezoneOffset;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsTimezoneOffsetTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsTimezoneOffset::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->test(''));
        isSame('', $rule->test('+00:00'));
        isSame('', $rule->test('-00:00'));
        isSame('', $rule->test('+12:30'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "1" is not a valid timezone offset. Example: "+03:00".',
            $rule->test('1'),
        );
        isSame(
            'Value "1:00" is not a valid timezone offset. Example: "+03:00".',
            $rule->test('1:00'),
        );
        isSame(
            'Value "01:00" is not a valid timezone offset. Example: "+03:00".',
            $rule->test('01:00'),
        );
        isSame(
            'Value "+25:00" is not a valid timezone offset. Example: "+03:00".',
            $rule->test('+25:00'),
        );
        isSame(
            'Value "+20:69" is not a valid timezone offset. Example: "+03:00".',
            $rule->test('+20:69'),
        );
        isSame(
            'Value "utc" is not a valid timezone offset. Example: "+03:00".',
            $rule->test('utc'),
        );
    }
}
