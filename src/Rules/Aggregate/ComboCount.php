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

final class ComboCount extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_COUNTER;

    protected const NAME = 'number of rows';

    public function getHelpMeta(): array
    {
        return [
            [
                'Total number of rows in the CSV file.',
                'Since any(!) values are taken into account, ' .
                'it only makes sense to use these rules once in any column.',
            ],
            [],
        ];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        return (int)\count($columnValues);
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        return \count($colValues);
    }
}
