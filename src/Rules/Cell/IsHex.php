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

final class IsHex extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Both: with or without "0x" prefix. Example: "0x1A" or "1A"',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid hexadecimal number. Example: \"0x1A\" or \"1A\"";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return \preg_match('/^[0-9a-f]+$/i', $cellValue) !== 0
            || \preg_match('/^0x[0-9a-f]+$/i', $cellValue) !== 0;
    }
}
