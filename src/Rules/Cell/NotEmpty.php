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

final class NotEmpty extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Value is not an empty string. Actually checks if the string length is not 0.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!$this->isEnabledByOption()) {
            return null;
        }

        if ($cellValue === '') {
            return 'Value is empty';
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return $cellValue !== '';
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        foreach ($columnValues as $cellValue) {
            if (!self::testValue($cellValue)) {
                return false;
            }
        }

        return true;
    }
}
