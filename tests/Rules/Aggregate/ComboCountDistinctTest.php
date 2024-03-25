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

use JBZoo\CsvBlueprint\Rules\AbstarctRule as Combo;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountDistinct;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboCountDistinctTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboCountDistinct::class;

    public function testEqual(): void
    {
        $rule = $this->create(1, Combo::EQ);

        isSame('', $rule->test(['']));
        isSame('', $rule->test(['1']));
        isSame('', $rule->test(['1', '1']));
        isSame('', $rule->test(['a', 'a']));

        isSame(
            'The number of unique values in the column is "2", which is not equal than the expected "1"',
            $rule->test(['a', 'a', 'b']),
        );
    }
}
