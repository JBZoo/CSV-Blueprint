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

use JBZoo\CsvBlueprint\Rules\Aggregate\IsSorted;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRule;

use function JBZoo\PHPUnit\isSame;

class IsSortedTest extends TestAbstractAggregateRule
{
    protected string $ruleClass = IsSorted::class;

    public function testPositive(): void
    {
        // natural
        $rule = $this->create(['asc', 'natural']);
        isSame('', $rule->test([]));
        isSame('', $rule->test(['item2', 'item10', 'item12']));

        $rule = $this->create(['desc', 'natural']);
        isSame('', $rule->test(['item12', 'item10', 'item2']));

        // regular
        $rule = $this->create(['asc', 'regular']);
        isSame('', $rule->test(['item10', 'item12', 'item2']));

        $rule = $this->create(['desc', 'regular']);
        isSame('', $rule->test(['item2', 'item12', 'item10']));

        // numeric
        $rule = $this->create(['asc', 'numeric']);
        isSame('', $rule->test(['Orange1', 'Orange3', 'orange2', 'orange20', '1', '2', '11', '20', '21']));

        $rule = $this->create(['desc', 'numeric']);
        isSame('', $rule->test(['21', '20', '11', '2', '1', 'orange20', 'orange2', 'Orange3', 'Orange1']));

        // string
        $rule = $this->create(['asc', 'string']);
        isSame('', $rule->test(['1', '11', '2', '20', '21', 'Orange1', 'Orange3', 'orange2', 'orange20']));

        $rule = $this->create(['desc', 'string']);
        isSame('', $rule->test(['orange20', 'orange2', 'Orange3', 'Orange1', '21', '20', '2', '11', '1']));
    }

    public function testNegative(): void
    {
        $rule = $this->create(['asc', 'natural']);
        isSame(
            'The column is not sorted "asc" using method "natural"',
            $rule->test(['1', '11', '2', '20', '21']),
        );

        $rule = $this->create(['QQQ', 'natural']);
        isSame(
            'Unknown sort direction: "QQQ". Allowed: ["asc", "desc"]',
            $rule->test(['1', '11', '2', '20', '21']),
        );

        $rule = $this->create(['asc', 'QQQQQQQ']);
        isSame(
            'Unknown sort method: "QQQQQQQ". Allowed: ["natural", "regular", "numeric", "string"]',
            $rule->test(['1', '11', '2', '20', '21']),
        );

        $rule = $this->create(['']);
        isSame(
            'The rule expects exactly two params: ' .
            'direction ["asc", "desc"] and method ["natural", "regular", "numeric", "string"]',
            $rule->test(['1', '11', '2', '20', '21']),
        );
    }
}
