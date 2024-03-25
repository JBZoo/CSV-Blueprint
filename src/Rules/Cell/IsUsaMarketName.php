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

use JBZoo\CsvBlueprint\Utils;

final class IsUsaMarketName extends AllowValues
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Check if the value is a valid USA market name. Example: "New York, NY"'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (Utils::testRegex('/^[A-Za-z\s\'\-\.,\(\)]+, [A-Z-]{2,6}$/u', $cellValue)) {
            return "Invalid market name format for value \"<c>{$cellValue}</c>\". " .
                'Market name must have format "<green>New York, NY</green>"';
        }

        return null;
    }
}
