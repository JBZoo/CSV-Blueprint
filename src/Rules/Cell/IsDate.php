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

final class IsDate extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Accepts arbitrary date format. Is shows error if failed to convert to timestamp.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid date.";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        if ($cellValue === '') {
            return true;
        }

        $shortLimit = 3;
        if (\strlen($cellValue) < $shortLimit) { // too short to ba a real date
            return false;
        }

        try {
            $result = new \DateTimeImmutable($cellValue); // attempt to parse it.
            return (bool)$result; // if no exception - it's a date
        } catch (\Exception) {
            return false;
        }
    }
}
