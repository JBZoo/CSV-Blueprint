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

namespace JBZoo\CsvBlueprint\Rules;

final class MaxLength extends AbstarctRule
{
    public function validateRule(?string $cellValue): ?string
    {
        $minLength = $this->getOptionAsInt();
        $length    = \mb_strlen((string)$cellValue);

        if ($length > $minLength) {
            return "Value \"<c>{$cellValue}</c>\" (length: {$length}) is too long. " .
                "Max length is <green>{$minLength}</green>";
        }

        return null;
    }
}
