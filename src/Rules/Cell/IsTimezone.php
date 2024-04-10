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

final class IsTimezone extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Allow only timezone identifiers. Case-insensitive. Example: "Europe/London", "utc".',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $zoneLowercase = \strtolower($cellValue);
        $expectedLowercase = \array_map('strtolower', \timezone_identifiers_list());

        if (!\in_array($zoneLowercase, $expectedLowercase, true)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid timezone identifier. " .
                'Example: "<green>Europe/London</green>".';
        }

        return null;
    }
}
