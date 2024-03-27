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

use JBZoo\CsvBlueprint\Rules\Cell\ContainsAll;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class ContainsAllTest extends TestAbstractCellRule
{
    protected string $ruleClass = ContainsAll::class;

    public function testPositive(): void
    {
        $rule = $this->create([]);
        isSame('', $rule->test(''));

        $rule = $this->create(['a', 'b', 'c']);
        isSame('', $rule->test('abc'));
        isSame('', $rule->test('abdasadasdasdc'));
    }

    public function testNegative(): void
    {
        $rule = $this->create([]);
        isSame(
            'Rule must contain at least one inclusion value in schema file.',
            $rule->test('ac'),
        );

        $rule = $this->create(['a', 'b', 'c']);
        isSame(
            'Value "ab" must contain all of the following: ["a", "b", "c"]',
            $rule->test('ab'),
        );
        isSame(
            'Value "ac" must contain all of the following: ["a", "b", "c"]',
            $rule->test('ac'),
        );
    }
}
