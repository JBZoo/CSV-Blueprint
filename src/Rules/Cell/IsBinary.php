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

final class IsBinary extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Both: with or without "0b" prefix. Example: "0b10" or "10"',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid binary number. Example: \"0b10\" or \"10\"";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return \preg_match('/^[01]+(_[01]+)*$/', $cellValue) !== 0
            || \preg_match('/^0[bB][01]+(_[01]+)*$/', $cellValue) !== 0;
    }
}
