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

use JBZoo\CsvBlueprint\Rules\AbstractRule;

final class ComboFirstNum extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'first value';

    public function getHelpMeta(): array
    {
        return [['First number in the column. Expected value is float or integer.'], []];
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (!isset($colValues[0])) {
            return null;
        }

        return (float)$colValues[0];
    }

    protected static function calcValue(array $columnValues, ?array $options = null): null|float|int
    {
        return null;
    }
}
