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

namespace JBZoo\CsvBlueprint\Validators\Rules;

final class MaxLength extends AbstarctRule
{
    public function validateRule(?string $cellValue): ?string
    {
        $minLength = $this->getOptionAsInt();
        $length    = \mb_strlen($cellValue);
        if (\mb_strlen($cellValue) > $minLength) {
            return "Value \"{$cellValue}\" (legth: {$length}) is too long. Max length is {$minLength}";
        }

        return null;
    }
}
