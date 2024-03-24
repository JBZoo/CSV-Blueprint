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

use JBZoo\CsvBlueprint\Rules\Aggregate\Last;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRule;

use function JBZoo\PHPUnit\isSame;

final class LastTest extends TestAbstractAggregateRule
{
    protected string $ruleClass = Last::class;

    public function testPositive(): void
    {
        $rule = $this->create('Value');
        isSame(null, $rule->validate([]));
        isSame(null, $rule->validate(['Value']));
        isSame(null, $rule->validate(['1', '2', '3', 'Value']));
    }

    public function testNegative(): void
    {
        $rule = $this->create('Value');
        isSame(
            'The last value in the column is "3", which is not equal than the expected "Value"',
            $rule->test(['1', 'Value', '2', '3']),
        );
    }
}
