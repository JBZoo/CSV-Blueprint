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

namespace JBZoo\PHPUnit\Rules;

use JBZoo\CsvBlueprint\Rules\Aggregate\IsUnique;
use JBZoo\PHPUnit\TestCase;

use function JBZoo\PHPUnit\isSame;

final class AggregateRulesTest extends TestCase
{
    protected function setUp(): void
    {
        \date_default_timezone_set('UTC');
    }

    public function testUnique(): void
    {
        $rule = new IsUnique('prop', true);
        isSame(null, $rule->validate([1]));
        isSame(null, $rule->validate([]));
        isSame(null, $rule->validate(['1', '2', '3']));
        isSame(
            '"ag:is_unique" at line 1, column "prop". Column has non-unique values. Unique: 3, total: 4.',
            \strip_tags((string)$rule->validate(['1', '2', '3', '3'])),
        );
    }
}
