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

final class IsJson extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [
                'Validates if the given input is a valid JSON.',
                'This is possible if you escape all special characters correctly and use a special CSV format.',
            ],
            [
                self::DEFAULT => ['true', 'Example: {"foo":"bar"}'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid JSON";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        // first symbol should be "{" or "["
        if (!\in_array($cellValue[0], ['{', '['], true)) {
            return false;
        }

        return Validator::json()->validate($cellValue);
    }
}
