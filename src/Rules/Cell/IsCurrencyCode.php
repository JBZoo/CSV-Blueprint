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

final class IsCurrencyCode extends AbstractCellRule
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => [
            'true',
            'Validates an ISO 4217 currency code like GBP or EUR. Case-sensitive. ' .
            'See: https://en.wikipedia.org/wiki/ISO_4217',
        ],
    ];

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        if (!Validator::currencyCode()->validate($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid currency code (ISO_4217)";
        }

        return null;
    }
}
