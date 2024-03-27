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

use JBZoo\CsvBlueprint\Rules\Cell\IpV4Range;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IpV4RangeTest extends TestAbstractCellRule
{
    protected string $ruleClass = IpV4Range::class;

    public function testPositive(): void
    {
        $rule = $this->create(['127.0.0.1-127.0.0.5', '127.0.0.0/21']);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('127.0.0.0'));
        isSame('', $rule->test('127.0.0.5'));
        isSame('', $rule->test('127.0.0.5'));
        isSame('', $rule->test('127.0.7.255'));

        $rule = $this->create(['127.0.0.1-127.0.0.5']);
        isSame('', $rule->test('127.0.0.1'));
        isSame('', $rule->test('127.0.0.2'));
        isSame('', $rule->test('127.0.0.5'));

        $rule = $this->create(['127.0.0.0/21']);
        isSame('', $rule->test('127.0.1.1'));
        isSame('', $rule->test('127.0.7.255'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(['127.0.0.1-127.0.0.5']);
        isSame(
            'Value "1.2.3" is not included in any of IPv4 the ranges: "127.0.0.1-127.0.0.5"',
            $rule->test('1.2.3'),
        );
        isSame(
            '"ip_v4_range" at line <red>1</red>, column "prop". ' .
            'Value "<c>1.2.3</c>" is not included in any of IPv4 the ranges: "<green>127.0.0.1-127.0.0.5</green>".',
            (string)$rule->validate('1.2.3'),
        );
        isSame(
            'Value "2001:0db8:85a3:08d3:1319:8a2e:0370:7334" is not included in any of IPv4 the ranges: ' .
            '"127.0.0.1-127.0.0.5"',
            $rule->test('2001:0db8:85a3:08d3:1319:8a2e:0370:7334'),
        );

        $rule = $this->create([]);
        isSame(
            'IPv4 range is not defined.',
            $rule->test('127.0.0.1'),
        );
    }
}
