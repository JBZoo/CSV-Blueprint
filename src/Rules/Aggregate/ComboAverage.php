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

namespace JBZoo\CsvBlueprint\Rules\Aggregate;

use MathPHP\Statistics\Average;

final class ComboAverage extends AbstarctAggregateRuleCombo
{
    protected const NAME = 'average';

    protected const HELP_TOP = ['Regular the arithmetic mean. The sum of the numbers divided by the count.'];

    protected function getActualAggregate(array $colValues): float
    {
        try {
            return Average::mean(self::stringsToFloat($colValues));
        } catch (\Exception) {
            return 0;
        }
    }
}
