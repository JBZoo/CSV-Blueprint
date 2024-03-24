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

use JBZoo\CsvBlueprint\Rules\Aggregate\FirstNot;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRule;

use function JBZoo\PHPUnit\isSame;

final class FirstNotTest extends TestAbstractAggregateRule
{
    protected string $ruleClass = FirstNot::class;

    public function testPositive(): void
    {
        $rule = $this->create('Value');
        isSame(null, $rule->validate([]));
        isSame(null, $rule->validate(['0', 'Value']));
        isSame(null, $rule->validate(['0', 'Value', '1', '2', '3']));
    }

    public function testNegative(): void
    {
        $rule = $this->create('Value');
        isSame(
            'The first value in the column is "Value", which is equal than the not allowed "Value"',
            $rule->test(['Value', '1', '2', '3']),
        );
    }
}
