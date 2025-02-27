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

final class IsPasswordSafeChars extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Check that the cell value contains only safe characters for regular passwords. '
                    . 'Allowed characters: a-z, A-Z, 0-9, !@#$%^&*()_+-=[]{};:\'"|,.<>/?~.',
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
            return "The value \"<c>{$cellValue}</c>\" as password uses not safe characters.";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return !Utils::testRegex('/^[a-zA-Z\d!@#$%^&*()_+\-=\[\]{};\':"\|,.<>\/?~]+$/', $cellValue);
    }
}
