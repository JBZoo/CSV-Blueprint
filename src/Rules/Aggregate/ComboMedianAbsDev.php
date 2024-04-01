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

use Ds\Vector;
use JBZoo\CsvBlueprint\Rules\AbstarctRule;
use MathPHP\Statistics\Descriptive;

final class ComboMedianAbsDev extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'MAD';

    public function getHelpMeta(): array
    {
        return [
            [
                'MAD - median absolute deviation. The average of the absolute deviations from a central point.',
                'It is a summary statistic of statistical dispersion or variability.',
                'It is a robust measure of the variability of a univariate sample of quantitative data.',
                'See: https://en.wikipedia.org/wiki/Median_absolute_deviation',
            ],
            [],
        ];
    }

    protected function getActualAggregate(Vector $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Descriptive::medianAbsoluteDeviation($colValues);
    }
}
