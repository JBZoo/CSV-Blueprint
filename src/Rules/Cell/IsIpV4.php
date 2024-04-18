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

final class IsIpV4 extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Only IPv4. Example: "127.0.0.1".'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid IPv4";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return Validator::ip('*', \FILTER_FLAG_IPV4)->validate($cellValue);
    }
}
