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

final class ComboCount extends AbstarctAggregateRuleCombo
{
    protected const NAME = 'number of rows';

    protected const HELP_TOP = [
        'Total number of rows in the CSV file.',
        'Since any(!) values are taken into account, it only makes sense to use these rules once in any column.',
    ];

    protected const HELP_OPTIONS = [
        self::EQ  => ['5', ''],
        self::NOT => ['4', ''],
        self::MIN => ['1', ''],
        self::MAX => ['10', ''],
    ];

    protected function getActualAggregate(array $colValues): ?float
    {
        return \count($colValues);
    }
}
