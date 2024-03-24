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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboMeanAbsDev;
use JBZoo\PHPUnit\Rules\TestAbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboMeanAbsDevTest extends TestAbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboMeanAbsDev::class;

    public function testEqual(): void
    {
        $rule = $this->create(3.5, Combo::EQ);
        isSame('', $rule->test(['_1', '   8.00   ']));

        $rule = $this->create(3, Combo::EQ);
        isSame(
            'The MAD in the column is "3.5", which is not equal than the expected "3"',
            $rule->test([1, 8]),
        );
    }
}
