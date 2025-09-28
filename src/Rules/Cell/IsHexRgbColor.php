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

final class IsHexRgbColor extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Validates whether the input is a hex RGB color or not. '
                    . 'Examples: "#FF0000", "#123", "ffffff", "fff".',
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
            return "Value \"<c>{$cellValue}</c>\" is not a valid hex RGB color.";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return Validator::hexRgbColor()->validate($cellValue);
    }
}
