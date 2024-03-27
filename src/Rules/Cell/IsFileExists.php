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

final class IsFileExists extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', "Check if file exists on the filesystem (It's FS IO operation!)."],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!\file_exists($cellValue)) {
            return "File \"<c>{$cellValue}</c>\" not found";
        }

        return null;
    }
}
