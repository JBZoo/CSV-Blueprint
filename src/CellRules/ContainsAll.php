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

final class ContainsAll extends AbstarctCellRule
{
    public function validateRule(string $cellValue): ?string
    {
        $inclusions = $this->getOptionAsArray();
        if (\count($inclusions) === 0) {
            return 'Rule must contain at least one inclusion value in schema file.';
        }

        foreach ($inclusions as $inclusion) {
            if (\strpos($cellValue, (string)$inclusion) === false) {
                return "Value \"<c>{$cellValue}</c>\" must contain all of the following:" .
                    ' "<green>["' . \implode('", "', $inclusions) . '"]</green>"';
            }
        }

        return null;
    }
}
