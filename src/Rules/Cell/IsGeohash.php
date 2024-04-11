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

final class IsGeohash extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Check if the value is a valid geohash. Example: "u4pruydqqvj"'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (Utils::testRegex('/^[0-9b-hj-km-np-z]{1,}$/', $cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid Geohash";
        }

        return null;
    }
}
