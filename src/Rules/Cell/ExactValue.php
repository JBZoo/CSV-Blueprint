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

final class ExactValue extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['Some string', 'Exact value for string in the column.'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        if ($this->getOptionAsString() !== $cellValue) {
            return "Value \"<c>{$cellValue}</c>\" is not strict equal to " .
                "\"<green>{$this->getOptionAsString()}</green>\"";
        }

        return null;
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $columnValues = \array_filter(\array_map('\strval', $columnValues), static fn (string $value) => $value !== '');
        $uniqueValues = \array_unique($columnValues);
        return \count($uniqueValues) === 1 ? $uniqueValues[0] : false;
    }
}
