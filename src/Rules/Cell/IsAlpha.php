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

final class IsAlpha extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'This is similar to `is_alnum`, but it does not allow numbers. Example: "aBc".',
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
            return "The value \"<c>{$cellValue}</c>\" should contain only alphabetic characters. " .
                'Example: "aBc"';
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return Validator::alpha()->validate($cellValue);
    }
}
