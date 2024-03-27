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

use JBZoo\CsvBlueprint\Rules\Cell\IsIpV6;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsIpV6Test extends TestAbstractCellRule
{
    protected string $ruleClass = IsIpV6::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('2001:0db8:85a3:08d3:1319:8a2e:0370:7334'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "1.2.3" is not a valid IPv6',
            $rule->test('1.2.3'),
        );
        isSame(
            'Value "127.0.0.1" is not a valid IPv6',
            $rule->test('127.0.0.1'),
        );
    }
}
