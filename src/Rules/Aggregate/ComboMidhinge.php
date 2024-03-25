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

final class ComboMidhinge extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'midhinge';

    public function getHelpMeta(): array
    {
        return [
            [
                'Midhinge. The average of the first and third quartiles and is thus a measure of location.',
                'Equivalently, it is the 25% trimmed mid-range or 25% midsummary; it is an L-estimator.',
                'See: https://en.wikipedia.org/wiki/Midhinge',
                'Midhinge = (first quartile, third quartile) / 2',
            ],
            [],
        ];
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Descriptive::midhinge(self::stringsToFloat($colValues));
    }
}
