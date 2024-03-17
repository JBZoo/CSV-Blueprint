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

final class IsUuid extends AbstractCellRule
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => [
            'true',
            'Validates whether the input is a valid UUID. ' .
            'It also supports validation of specific versions 1, 3, 4 and 5.',
        ],
    ];

    public function validateRule(string $cellValue): ?string
    {
        if (!Validator::uuid()->validate($cellValue)) {
            return 'Value is not a valid UUID';
        }

        return null;
    }
}
