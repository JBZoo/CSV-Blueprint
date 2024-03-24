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

use JBZoo\CsvBlueprint\Rules\AbstarctRule;
use MathPHP\Statistics\Average;

final class ComboMedian extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'median';

    protected const HELP_TOP = ['Calculate the median average of a list of numbers.'];

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Average::median(self::stringsToFloat($colValues));
    }
}
