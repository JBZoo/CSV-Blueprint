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

final class IsJson extends AbstractCellRule
{
    protected const HELP_TOP = [
        'Validates if the given input is a valid JSON.',
        'This is possible if you escape all special characters correctly and use a special CSV format.',
    ];

    protected const HELP_OPTIONS = [
        self::DEFAULT => [
            'true',
            'Example: {"foo":"bar"}',
        ],
    ];

    public function validateRule(string $cellValue): ?string
    {
        if (!Validator::json()->validate($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid JSON";
        }

        return null;
    }
}
