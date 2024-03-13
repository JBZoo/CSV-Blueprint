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

final class MinPrecision extends Precision
{
    public function validateRule(?string $cellValue): ?string
    {
        $valuePrecision = self::getFloatPrecision($cellValue);

        if ($valuePrecision < $this->getOptionAsInt()) {
            return "Value \"<c>{$cellValue}</c>\" has a precision of {$valuePrecision} " .
                "but should have a min precision of <green>{$this->getOptionAsInt()}</green>";
        }

        return null;
    }
}
