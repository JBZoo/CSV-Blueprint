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

use JBZoo\CsvBlueprint\Rules\Cell\AllowValues;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class AllowValuesTest extends TestAbstractCellRule
{
    protected string $ruleClass = AllowValues::class;

    public function testPositive(): void
    {
        $rule = $this->create(['1', '2', '3']);
        isSame('', $rule->test('1'));
        isSame('', $rule->test('2'));
        isSame('', $rule->test('3'));

        $rule = $this->create(['1', '2', '3', '']);
        isSame('', $rule->test(''));

        $rule = $this->create(['1', '2', '3', ' ']);
        isSame('', $rule->test(' '));
    }

    public function testNegative(): void
    {
        $rule = $this->create(['1', '2', '3']);
        isSame(
            'Value "invalid" is not allowed. Allowed values: ["1", "2", "3"]',
            $rule->test('invalid'),
        );

        $rule = $this->create([]);
        isSame(
            'Allowed values are not defined',
            $rule->test('invalid'),
        );
    }
}
