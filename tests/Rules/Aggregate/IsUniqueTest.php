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

use JBZoo\CsvBlueprint\Rules\Aggregate\IsUnique;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRule;

use function JBZoo\PHPUnit\isSame;

final class IsUniqueTest extends TestAbstractAggregateRule
{
    protected string $ruleClass = IsUnique::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate([1]));
        isSame(null, $rule->validate([]));
        isSame(null, $rule->validate(['1', '2', '3']));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Column has non-unique values. Unique: 3, total: 4',
            $rule->test(['1', '2', '3', '3']),
        );
    }
}
