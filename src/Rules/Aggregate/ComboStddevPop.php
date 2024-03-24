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
use MathPHP\Statistics\Descriptive;

final class ComboStddevPop extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'standard deviation (SD+)';

    protected const HELP_TOP = [
        'SD+ (Standard deviation for a population; uses population variance)',
    ];

    protected function getActualAggregate(array $colValues): ?float
    {
        return Descriptive::standardDeviation(self::stringsToFloat($colValues), Descriptive::POPULATION);
    }
}
