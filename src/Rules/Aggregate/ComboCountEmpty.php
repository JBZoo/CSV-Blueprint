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

final class ComboCountEmpty extends AbstarctAggregateRuleCombo
{
    protected const NAME = 'number of empty rows';

    protected const HELP_TOP = ['Counts only empty values (string length is 0).'];

    protected const HELP_OPTIONS = [
        self::EQ  => ['5', ''],
        self::NOT => ['4', ''],
        self::MIN => ['1', ''],
        self::MAX => ['10', ''],
    ];

    protected function getActualAggregate(array $colValues): ?float
    {
        return \count(\array_filter($colValues, static fn ($colValue) => $colValue === ''));
    }
}
