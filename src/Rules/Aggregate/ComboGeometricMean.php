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

final class ComboGeometricMean extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'geometric mean';

    public function getHelpMeta(): array
    {
        return [
            [
                'Geometric mean. A type of mean which indicates the central tendency or typical value of' .
                ' a set of numbers',
                'by using the product of their values (as opposed to the arithmetic mean which uses their sum).',
                'See: https://en.wikipedia.org/wiki/Geometric_mean',
            ],
            [],
        ];
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Average::geometricMean($colValues);
    }
}
