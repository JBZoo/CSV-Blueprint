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

use function JBZoo\Utils\float;

final class ComboNum extends AbstractCellRuleCombo
{
    protected const NAME = '';

    private const PRECISION = 10;

    public function getHelpMeta(): array
    {
        return [
            [
                'Under the hood it converts and compares as float values.',
                'Comparison accuracy is ' . self::PRECISION . ' digits after a dot.',
                'Scientific number format is also supported. Example: "1.2e3"',
            ],
            [],
        ];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $min = null;
        $max = null;

        foreach ($columnValues as $cellValue) {
            if (!IsFloat::testValue($cellValue) && $cellValue !== '') {
                return false;
            }

            $float = (float)$cellValue;
            if ($min === null || $float < $min) {
                $min = $float;
            }
            if ($max === null || $float > $max) {
                $max = $float;
            }
        }

        if ($min === null) {
            return false;
        }

        return $max === $min
            ? ['' => $max]
            : ['min' => $min, 'max' => $max];
    }

    protected function getExpected(): float
    {
        return float($this->getOptionAsString(), self::PRECISION);
    }

    protected function getCurrentStr(string $cellValue): string
    {
        return '';
    }

    protected function getActualCell(string $cellValue): float
    {
        return float($cellValue, self::PRECISION);
    }
}
