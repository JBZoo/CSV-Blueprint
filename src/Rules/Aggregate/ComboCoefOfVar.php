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

final class ComboCoefOfVar extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'Coefficient of variation';

    public function getHelpMeta(): array
    {
        return [
            [
                'Coefficient of variation (cᵥ) Also known as relative standard deviation (RSD)',
                'A standardized measure of dispersion of a probability distribution or frequency distribution.',
                'It is often expressed as a percentage. The ratio of the standard deviation to the mean.',
                'See: https://en.wikipedia.org/wiki/Coefficient_of_variation',
            ],
            [],
        ];
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Descriptive::coefficientOfVariation(self::stringsToFloat($colValues));
    }
}
