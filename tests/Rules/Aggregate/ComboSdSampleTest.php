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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboSdSample;
use JBZoo\PHPUnit\Rules\AbstractAggregateRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboSdSampleTest extends AbstractAggregateRuleCombo
{
    protected string $ruleClass = ComboSdSample::class;

    public function testEqual(): void
    {
        $rule = $this->create(2.5, Combo::EQ);
        isSame('', $rule->test([1, 5, 1, 1, 1, 2, 8, 1, 1]));

        $rule = $this->create(3, Combo::EQ);
        isSame(
            'The standard deviation (SD) in the column is "2.5", which is not equal than the expected "3"',
            $rule->test([1, 5, 1, 1, 1, 2, 8, 1, 1]),
        );
    }
}