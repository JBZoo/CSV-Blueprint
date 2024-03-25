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

final class ComboLastNum extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'last value';

    public function getHelpMeta(): array
    {
        return [['Last number in the column. Expected value is float or integer.'], []];
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return (float)\end($colValues);
    }
}
