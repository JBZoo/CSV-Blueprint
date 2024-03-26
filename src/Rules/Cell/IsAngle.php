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

final class IsAngle extends IsFloat
{
    private float $min = 0.0;
    private float $max = 360.0;

    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Check if the cell value is a valid angle (0.0 to 360.0).'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        $result = parent::validateRule($cellValue);
        if ($result !== null) {
            return $result;
        }

        $angle = (float)$cellValue;
        if ($angle < $this->min || $angle > $this->max) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid angle <green>({$this->min} to {$this->max})</green>";
        }

        return null;
    }
}
