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

namespace JBZoo\PHPUnit\Rules\Aggregate;

use JBZoo\CsvBlueprint\Rules\Aggregate\NthNot;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRule;

use function JBZoo\PHPUnit\isSame;

final class NthNotTest extends TestAbstractAggregateRule
{
    protected string $ruleClass = NthNot::class;

    public function testPositive(): void
    {
        $rule = $this->create([2, 'Value']);
        isSame(null, $rule->validate([]));
        isSame(null, $rule->validate(['', 'Value', '']));
    }

    public function testNegative(): void
    {
        $rule = $this->create('Value');
        isSame(
            '"ag:nth_not" at line 1, column "prop". ' .
            'Unexpected error: Invalid option "Value" for the "ag:nth_not" rule. It should be array of strings.',
            \strip_tags((string)$rule->validate(['1', 'Value', '2', '3'])),
        );

        $rule = $this->create([]);
        isSame(
            'The rule expects exactly two arguments: ' .
            'the first is the line number, the second is the not expected value',
            $rule->test(['1', 'Value', '2', '3']),
        );

        $rule = $this->create([2, 'Value']);
        isSame(
            'The 2 value in the column is "Value", which is equal than the not expected "Value"',
            $rule->test(['1', '2', 'Value', '3']),
        );
    }
}
