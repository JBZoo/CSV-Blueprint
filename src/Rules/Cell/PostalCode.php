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

class PostalCode extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'US',
                    'Validate postal code by country code (alpha-2). ' .
                    'Example: "02179". Extracted from https://www.geonames.org',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $countryCode = $this->getOptionAsString();
        if ($countryCode === '') {
            return 'Country code is not defined. Please, set it in the rule options.';
        }

        if (!Validator::postalCode($countryCode)->validate($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid postal code for country \"{$countryCode}\".";
        }

        return null;
    }
}
