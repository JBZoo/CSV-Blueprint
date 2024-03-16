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

final class IsLongitude extends IsFloat
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => ['true', 'Can be integer or float. Example: -89.123456'],
    ];

    private float $min = -180.0;
    private float $max = 180.0;

    public function validateRule(string $cellValue): ?string
    {
        $result = parent::validateRule($cellValue);
        if ($result !== null) {
            return $result;
        }

        $longitude = (float)$cellValue;
        if ($longitude < $this->min || $longitude > $this->max) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid longitude " .
                "<green>({$this->min} -> {$this->max})</green>";
        }

        return null;
    }
}
