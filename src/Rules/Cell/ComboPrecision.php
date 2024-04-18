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

use Respect\Validation\Validator;

final class ComboPrecision extends AbstractCellRuleCombo
{
    protected const NAME = 'precision';

    public function getHelpMeta(): array
    {
        return [['Number of digits after the decimal point (with zeros)'], []];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|string
    {
        $min = null;
        $max = null;

        foreach ($columnValues as $cellValue) {
            if (
                !Validator::floatVal()->validate($cellValue)
                && !Validator::intVal()->validate($cellValue)
            ) {
                return false;
            }

            $precision = self::getFloatPrecision($cellValue);

            if ($min === null || $precision < $min) {
                $min = $precision;
            }

            if ($max === null || $precision > $max) {
                $max = $precision;
            }
        }

        return $max === $min
            ? ['' => $max]
            : ['min' => $min, 'max' => $max];
    }

    protected function getExpected(): float
    {
        return $this->getOptionAsInt();
    }

    protected function getActualCell(string $cellValue): float
    {
        return self::getFloatPrecision($cellValue);
    }

    private static function getFloatPrecision(string $cellValue): int
    {
        $dotPosition = \strpos($cellValue, '.');
        if ($dotPosition === false) {
            return 0;
        }

        return \strlen($cellValue) - $dotPosition - 1;
    }
}
