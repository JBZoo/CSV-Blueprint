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

final class IsLowercase extends AbstarctCellRule
{
    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue !== \mb_strtolower($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" should be in lowercase";
        }

        return null;
    }
}
