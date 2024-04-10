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

class IsTime extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Check if the cell value is a valid time in the format ' .
                    '"HH:MM:SS AM/PM" / "HH:MM:SS" / "HH:MM". Case-insensitive.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $regexs = [
            '1:59'        => '/^([1-9]|1[0-2]):[0-5]\d$/',
            '23:59'       => '/^(?:2[0-3]|[01]\d):[0-5]\d$/',
            '23:59:59'    => '/^(?:2[0-3]|[01]?\d):[0-5]\d:[0-5]\d$/',
            '1:59 am'     => '/^([1-9]|1[0-2]):[0-5]\d\s?(am|pm)$/i',
            '12:59 am'    => '/^(1[0-2]|0?[1-9]):[0-5]\d\s?(am|pm)$/i',
            '12:59:59 am' => '/^(1[0-2]|0?[1-9]):[0-5]\d:[0-5]\d\s?(am|pm)$/i',
        ];

        foreach ($regexs as $regex) {
            if (\preg_match($regex, $cellValue) > 0) {
                return null;
            }
        }

        return "Value \"<c>{$cellValue}</c>\" is not a valid time. " .
            'Example: "<green>12:34:56 AM</green>", "<green>23:34:56</green>", "<green>12:34</green>"';
    }
}
