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

final class IsTrimmed extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Only trimmed strings. Example: "Hello World" (not " Hello World ").'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not trimmed";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return \trim($cellValue) === $cellValue;
    }
}
