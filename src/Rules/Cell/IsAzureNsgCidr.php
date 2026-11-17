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

final class IsAzureNsgCidr extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Azure NSG address prefix: "*", IPv4/IPv6 address or CIDR, or service tag. Case-insensitive. '
                    . 'Example: "10.0.0.0/24", "VirtualNetwork", "Storage.WestEurope".',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testPrefix($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid Azure NSG address prefix";
        }

        return null;
    }

    private static function testPrefix(string $cellValue): bool
    {
        if ($cellValue === '*' || \preg_match('/^[a-z][a-z\d]*(\.[a-z][a-z\d]*)?$/iD', $cellValue) === 1) {
            return true;
        }

        if (!\str_contains($cellValue, '/')) {
            return IsIp::testValue($cellValue);
        }

        [$address, $prefixLength] = \explode('/', $cellValue, 2);

        if (!IsIp::testValue($address) || \preg_match('/^(0|[1-9]\d{0,2})$/D', $prefixLength) !== 1) {
            return false;
        }

        return (int)$prefixLength <= (IsIpV4::testValue($address) ? 32 : 128);
    }
}
