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

use JBZoo\Utils\Filter;

final class IsAlias extends AbstractCellRule
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => [
            'true',
            'Only alias format. Example: "my-alias-123". It can contain letters, numbers, and dashes.',
        ],
    ];

    public function validateRule(string $cellValue): ?string
    {
        $alias = Filter::alias($cellValue);
        if ($cellValue !== $alias) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid alias. Expected \"<green>{$alias}</green>\".";
        }

        return null;
    }
}
