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

use JBZoo\CsvBlueprint\Utils;

final class Regex extends AbstarctCellRule
{
    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $regex = Utils::prepareRegex($this->getOptionAsString());
        if ($regex === null || $regex === '') {
            return 'Regex pattern is not defined';
        }

        if (\preg_match($regex, $cellValue) === 0) {
            return "Value \"<c>{$cellValue}</c>\" does not match the pattern \"<green>{$regex}</green>\"";
        }

        return null;
    }
}
