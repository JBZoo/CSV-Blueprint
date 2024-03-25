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

final class IsUrl extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Only URL format. Example: "https://example.com/page?query=string#anchor"'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (\filter_var($cellValue, \FILTER_VALIDATE_URL) === false) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid URL";
        }

        return null;
    }
}
