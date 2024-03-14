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

final class Contains extends AbstarctCellRule
{
    public function validateRule(string $cellValue): ?string
    {
        $expected = $this->getOptionAsString();

        if ($expected === '') {
            return 'Rule must contain at least one char in schema file.';
        }

        if (\strpos($cellValue, $expected) === false) {
            return "Value \"<c>{$cellValue}</c>\" must contain \"<green>{$expected}</green>\"";
        }

        return null;
    }
}
