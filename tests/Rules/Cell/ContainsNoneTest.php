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

use JBZoo\CsvBlueprint\Rules\Cell\ContainsNone;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class ContainsNoneTest extends TestAbstractCellRule
{
    protected string $ruleClass = ContainsNone::class;

    public function testPositive(): void
    {
        $rule = $this->create([]);
        isSame('', $rule->test(''));

        $rule = $this->create(['a', 'b', 'c']);
        isSame('', $rule->test(''));
        isSame('', $rule->test('q'));
    }

    public function testNegative(): void
    {
        $rule = $this->create([]);
        isSame(
            'Rule must contain at least one exclusion value in schema file.',
            $rule->test('ac'),
        );

        $rule = $this->create(['a', 'b', 'c']);
        isSame(
            'Value "a" must not contain any of the following: "["a", "b", "c"]"',
            $rule->test('a'),
        );

        $rule = $this->create(['a', 'b', 'c']);
        isSame(
            'Value "ddddb" must not contain any of the following: "["a", "b", "c"]"',
            $rule->test('ddddb'),
        );
    }
}
