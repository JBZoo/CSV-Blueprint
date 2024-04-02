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

final class IsOctal extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Validates octal numbers in the format "0o123" or "0123".',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (
            \preg_match('/^[0-7]+(_[0-7]+)*$/', $cellValue) === 0
            && \preg_match('/^0[oO]?[0-7]+(_[0-7]+)*$/', $cellValue) === 0
        ) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid octal number. Examples: \"0o123\" or \"0123\"";
        }

        return null;
    }
}
