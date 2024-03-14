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

final class IsUsaMarketName extends AllowValues
{
    public function validateRule(string $cellValue): ?string
    {
        if (\preg_match('/^[A-Za-z0-9\s-]+, [A-Z]{2}$/u', $cellValue) === 0) {
            return "Invalid market name format for value \"<c>{$cellValue}</c>\". " .
                'Market name must have format "<green>New York, NY</green>"';
        }

        return null;
    }
}
