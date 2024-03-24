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

final class ComboFirstNum extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'first value';

    protected const HELP_TOP = ['First number in the column. Expected value is float or integer.'];

    protected const HELP_OPTIONS = [
        self::EQ  => ['5', ''],
        self::NOT => ['4.123', ''],
        self::MIN => ['-1', ''],
        self::MAX => ['2e4', ''],
    ];

    protected function getActualAggregate(array $colValues): ?float
    {
        if (!isset($colValues[0])) {
            return null;
        }

        return (float)$colValues[0];
    }
}
