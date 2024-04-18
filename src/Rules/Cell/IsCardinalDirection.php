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

use JBZoo\CsvBlueprint\Utils;

final class IsCardinalDirection extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Valid cardinal direction. Case-insensitive. ' .
                    'Available values: ' . Utils::printList(self::getOptions()),
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not allowed. Allowed values: " .
                Utils::printList(self::getOptions());
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return \in_array(\strtoupper($cellValue), self::getOptions(), true);
    }

    private static function getOptions(): array
    {
        return ['N', 'S', 'E', 'W', 'NE', 'SE', 'NW', 'SW', 'NONE'];
    }
}
