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

use MathPHP\Statistics\Descriptive;

final class ComboSdSample extends AbstarctAggregateRuleCombo
{
    protected const NAME = 'standard deviation (SD)';

    protected const HELP_TOP = [
        'Standard deviation (For a sample; uses sample variance)',
        'Standard deviation is a measure that is used to quantify the amount of variation or ' .
        'dispersion of a set of data values.',
        'A low standard deviation indicates that the data points tend to be close to the mean ' .
        '(also called the expected value) of the set.',
        'A high standard deviation indicates that the data points are spread out over a wider range of values.',
        'See: https://en.wikipedia.org/wiki/Standard_deviation',
    ];

    protected function getActualAggregate(array $colValues): ?float
    {
        return Descriptive::standardDeviation(self::stringsToFloat($colValues));
    }
}
