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

use JBZoo\CsvBlueprint\Rules\Cell\IsIpReserved;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsIpReservedTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsIpReserved::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0.0.0.0'));
        isSame('', $rule->test('127.0.0.1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "45.46.47.48" is not a reserved IP address.',
            $rule->test('45.46.47.48'),
        );
        isSame(
            'Value "fd00:0000:0000:0000:0000:0000:0000:0001" is not a reserved IP address.',
            $rule->test('fd00:0000:0000:0000:0000:0000:0000:0001'),
        );
        isSame(
            'Value "fc00:0000:0000:0000:0000:0000:0000:0001" is not a reserved IP address.',
            $rule->test('fc00:0000:0000:0000:0000:0000:0000:0001'),
        );
    }
}
