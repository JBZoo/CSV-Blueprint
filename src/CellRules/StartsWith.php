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

final class StartsWith extends AbstarctCellRule
{
    public function validateRule(string $cellValue): ?string
    {
        $prefix = $this->getOptionAsString();
        if ($prefix === '') {
            return 'Rule must contain a prefix value in schema file.';
        }

        if (!\str_starts_with($cellValue, $prefix)) {
            return "Value \"<c>{$cellValue}</c>\" must start with \"<green>{$prefix}</green>\"";
        }

        return null;
    }
}
