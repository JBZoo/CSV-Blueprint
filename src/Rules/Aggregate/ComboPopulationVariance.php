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

final class ComboPopulationVariance extends AbstarctAggregateRuleCombo
{
    protected const NAME = 'population variance';

    protected const HELP_TOP = [
        'Population variance - Use when all possible observations of the system are present.',
        'If used with a subset of data (sample variance), it will be a biased variance.',
        'It\'s n degrees of freedom',
    ];

    protected function getActualAggregate(array $colValues): ?float
    {
        return Descriptive::populationVariance(self::stringsToFloat($colValues));
    }
}
