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

namespace JBZoo\CsvBlueprint\CellRules;

class Precision extends AbstarctCellRule
{
    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $valuePrecision = self::getFloatPrecision($cellValue);

        if ($valuePrecision !== $this->getOptionAsInt()) {
            return "Value \"<c>{$cellValue}</c>\" has a precision of {$valuePrecision} " .
                "but should have a precision of <green>{$this->getOptionAsInt()}</green>";
        }

        return null;
    }

    protected static function getFloatPrecision(string $cellValue): int
    {
        $dotPosition = \strpos($cellValue, '.');
        if ($dotPosition === false) {
            return 0;
        }

        return \strlen($cellValue) - $dotPosition - 1;
    }
}
