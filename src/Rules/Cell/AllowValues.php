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

namespace JBZoo\CsvBlueprint\Rules\Cell;

use JBZoo\CsvBlueprint\Utils;

final class AllowValues extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['[ y, n, "" ]', 'Strict set of values that are allowed.']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        $allowedValues = \array_map('\strval', $this->getOptionAsArray());

        if (\count($allowedValues) === 0) {
            return 'Allowed values are not defined';
        }

        if (!\in_array($cellValue, $allowedValues, true)) {
            return "Value \"<c>{$cellValue}</c>\" is not allowed. "
                . 'Allowed values: ' . Utils::printList($allowedValues, 'green');
        }

        return null;
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $maxLimit = 5;

        $columnValues = \array_filter(\array_map('\strval', $columnValues), static fn (string $value) => $value !== '');
        $uniqueValues = \array_unique($columnValues);
        if (\count($uniqueValues) > $maxLimit) {
            return false;
        }

        \asort($uniqueValues, \SORT_NATURAL);

        return \array_values($uniqueValues);
    }
}
