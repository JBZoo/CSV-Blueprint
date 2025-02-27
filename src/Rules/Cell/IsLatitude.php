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

use Respect\Validation\Validator;

final class IsLatitude extends AbstractCellRule
{
    public const MIN_VALUE = -90.0;
    public const MAX_VALUE = 90.0;

    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Can be integer or float. Example: 50.123456'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid latitude "
                . '(' . self::MIN_VALUE . ' to ' . self::MAX_VALUE . ')';
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        if (!Validator::floatVal()->validate($cellValue)) {
            return false;
        }

        $latitude = (float)$cellValue;
        return !($latitude < self::MIN_VALUE || $latitude > self::MAX_VALUE);
    }
}
