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

use JBZoo\CsvBlueprint\Rules\Cell\DateFormat;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class DateFormatTest extends TestAbstractCellRule
{
    protected string $ruleClass = DateFormat::class;

    public function testPositive(): void
    {
        $rule = $this->create('Y-m-d');
        isSame('', $rule->test(''));
        isSame('', $rule->test('2000-12-31'));
    }

    public function testNegative(): void
    {
        $rule = $this->create('Y-m-d');
        isSame(
            'Date format of value "12" is not valid. Expected format: "Y-m-d"',
            $rule->test('12'),
        );
        isSame(
            'Date format of value "2000-01-02 12:34:56" is not valid. Expected format: "Y-m-d"',
            $rule->test('2000-01-02 12:34:56'),
        );

        $rule = $this->create('');
        isSame(
            'Date format is not defined',
            $rule->test('12'),
        );
    }
}
