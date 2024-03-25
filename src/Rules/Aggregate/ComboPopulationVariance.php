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

final class ComboPopulationVariance extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'population variance';

    public function getHelpMeta(): array
    {
        return [
            [
                'Population variance - Use when all possible observations of the system are present.',
                'If used with a subset of data (sample variance), it will be a biased variance.',
                'n degrees of freedom, where n is the number of observations.',
            ],
            [],
        ];
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Descriptive::populationVariance(self::stringsToFloat($colValues));
    }
}
