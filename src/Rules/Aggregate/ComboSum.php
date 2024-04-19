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
use JBZoo\CsvBlueprint\Utils;

final class ComboSum extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'sum of numbers';

    public function getHelpMeta(): array
    {
        return [['Sum of the numbers in the column. Example: [1, 2, 3] => 6.'], []];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $result = self::calcValue($columnValues);
        if ($result === null) {
            return false;
        }

        return $result;
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        return self::calcValue($colValues);
    }

    protected static function calcValue(array $columnValues, ?array $options = null): null|float|int
    {
        $columnValues = Utils::analyzeGuard($columnValues, self::INPUT_TYPE);
        if ($columnValues === null) {
            return null;
        }

        return \array_sum($columnValues);
    }
}
