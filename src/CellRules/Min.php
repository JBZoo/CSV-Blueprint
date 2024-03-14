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

final class Min extends IsFloat
{
    public function validateRule(string $cellValue): ?string
    {
        $result = parent::validateRule($cellValue);
        if ($result !== null) {
            return $result;
        }

        if ($this->getOptionAsFloat() > (float)$cellValue) {
            return "Value \"<c>{$cellValue}</c>\" is less than \"<green>{$this->getOptionAsFloat()}</green>\"";
        }

        return null;
    }
}
