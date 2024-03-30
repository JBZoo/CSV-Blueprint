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
use MathPHP\Statistics\Average;

final class ComboHarmonicMean extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'harmonic mean';

    public function getHelpMeta(): array
    {
        return [
            [
                'Harmonic mean (subcontrary mean). The harmonic mean can be expressed as the reciprocal of ' .
                'the arithmetic mean of the reciprocals.',
                'Appropriate for situations when the average of rates is desired.',
                'See: https://en.wikipedia.org/wiki/Harmonic_mean',
            ],
            [],
        ];
    }

    protected function getActualAggregate(Vector $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Average::harmonicMean($colValues);
    }
}
