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

final class IsIpPrivate extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'IPv4 has ranges: 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16. '
                    . 'IPv6 has ranges starting with FD or FC.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a private IP address.";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        return Validator::ip('*', \FILTER_FLAG_NO_PRIV_RANGE)->validate($cellValue);
    }
}
