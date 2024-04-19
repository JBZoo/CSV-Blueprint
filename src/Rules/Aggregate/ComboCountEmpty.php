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

final class ComboCountEmpty extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_STRINGS;

    protected const NAME = 'number of empty rows';

    public function getHelpMeta(): array
    {
        return [['Counts only empty values (string length is 0).'], []];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        return (int)\count(\array_filter($columnValues, static fn ($colValue) => $colValue === ''));
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return \count(\array_filter($colValues, static fn ($colValue) => $colValue === ''));
    }
}
