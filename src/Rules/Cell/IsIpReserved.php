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

final class IsIpReserved extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'IPv4 has ranges: 0.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8 and 240.0.0.0/4. '
                    . 'IPv6 has ranges: ::1/128, ::/128, ::ffff:0:0/96 and fe80::/10.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a reserved IP address.";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return Validator::ip('*', \FILTER_FLAG_NO_RES_RANGE)->validate($cellValue);
    }
}
