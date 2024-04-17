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

final class IsUnique extends AbstractAggregateRule
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_STRINGS;

    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['true', 'All values in the column are unique.']],
        ];
    }

    public function validateRule(array $columnValues): ?string
    {
        if (\count($columnValues) === 0) {
            return null;
        }

        if (self::analyzeColumnValues($columnValues) === false) {
            $uValuesCount = \count(\array_unique($columnValues));
            $valuesCount = \count($columnValues);

            return 'Column has non-unique values. ' .
                "Unique: <c>{$uValuesCount}</c>, total: <green>{$valuesCount}</green>";
        }

        return null;
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|string
    {
        $uValuesCount = \count(\array_unique($columnValues));
        $valuesCount = \count($columnValues);
        return $uValuesCount === $valuesCount;
    }
}
