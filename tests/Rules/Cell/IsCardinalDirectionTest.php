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

use JBZoo\CsvBlueprint\Rules\Cell\IsCardinalDirection;
use JBZoo\PHPUnit\Rules\AbstractCellRuleTest;

use function JBZoo\PHPUnit\isSame;

final class IsCardinalDirectionTest extends AbstractCellRuleTest
{
    protected string $ruleClass = IsCardinalDirection::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->test(''));
        isSame('', $rule->test('N'));
        isSame('', $rule->test('S'));
        isSame('', $rule->test('E'));
        isSame('', $rule->test('W'));
        isSame('', $rule->test('NE'));
        isSame('', $rule->test('SE'));
        isSame('', $rule->test('NW'));
        isSame('', $rule->test('SW'));
        isSame('', $rule->test('none'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "qwe" is not allowed. ' .
            'Allowed values: ["N", "S", "E", "W", "NE", "SE", "NW", "SW", "none", ""]',
            $rule->test('qwe'),
        );

        isSame(
            'Value "n" is not allowed. ' .
            'Allowed values: ["N", "S", "E", "W", "NE", "SE", "NW", "SW", "none", ""]',
            $rule->test('n'),
        );
    }
}
