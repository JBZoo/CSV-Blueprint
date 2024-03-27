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

use JBZoo\CsvBlueprint\Rules\Cell\IsIpPrivate;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsIpPrivateTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsIpPrivate::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('10.0.0.1'));
        isSame('', $rule->test('fc01:0db8:85a3:08d3:1319:8a2e:0370:7334'));
        isSame('', $rule->test('fd01:0db8:85a3:08d3:1319:8a2e:0370:7334'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "189.0.0.1" is not a private IP address.',
            $rule->test('189.0.0.1'),
        );
        isSame(
            'Value "2020:0000:0000:0000:0000:0000:0000:0001" is not a private IP address.',
            $rule->test('2020:0000:0000:0000:0000:0000:0000:0001'),
        );
    }
}
