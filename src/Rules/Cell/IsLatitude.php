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

final class IsLatitude extends IsFloat
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => ['true', 'Can be integer or float. Example: 50.123456'],
    ];

    private float $min = -90.0;
    private float $max = 90.0;

    public function validateRule(string $cellValue): ?string
    {
        $result = parent::validateRule($cellValue);
        if ($result !== null) {
            return $result;
        }

        $latitude = (float)$cellValue;
        if ($latitude < $this->min || $latitude > $this->max) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid latitude ({$this->min} -> {$this->max})";
        }

        return null;
    }
}
