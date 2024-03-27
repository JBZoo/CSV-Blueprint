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

use Respect\Validation\Rules\CountryCode as RespectCountryCode;
use Respect\Validation\Validator;

class IsCountryCode extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [
                'Validates whether the input is a country code in ISO 3166-1 standard.',
                'Available options: "alpha-2" (Ex: "US"), "alpha-3" (Ex: "USA"), "numeric" (Ex: "840").',
                'The rule uses data from iso-codes: https://salsa.debian.org/iso-codes-team/iso-codes.',
            ],
            [
                self::DEFAULT => ['alpha-2', 'Country code in ISO 3166-1 standard. Examples: "US", "USA", "840"'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $validSets = [
            RespectCountryCode::ALPHA2,
            RespectCountryCode::ALPHA3,
            RespectCountryCode::NUMERIC,
        ];

        $set = $this->getOptionAsString();

        if (!\in_array($set, $validSets, true)) {
            return "Unknown country set: \"<c>{$set}</c>\". " .
                'Available options: [<green>' . \implode(', ', $validSets) . '</green>]';
        }

        if (!Validator::countryCode($set)->validate($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid {$set} country code.";
        }

        return null;
    }
}
