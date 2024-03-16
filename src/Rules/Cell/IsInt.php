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

final class IsInt extends AbstarctCellRule
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => ['true', 'Check format only. Can be negative and positive. Without any separators'],
    ];

    public function validateRule(string $cellValue): ?string
    {
        if (\preg_match('/^-?\d+$/', $cellValue) === 0) {
            return "Value \"<c>{$cellValue}</c>\" is not an integer";
        }

        return null;
    }
}
