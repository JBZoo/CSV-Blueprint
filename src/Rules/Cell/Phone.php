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

final class Phone extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'ALL',
                    'Validates if the input is a phone number. Specify the country code to validate the phone number '
                    . 'for a specific country. Example: "ALL", "US", "BR".',
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
            return 'The country code is required. Example: "ALL", "US", "BR"';
        }

        if ($countryCode === 'ALL') {
            $countryCode = null;
        }

        // @phpstan-ignore-next-line
        if (!Validator::phone($countryCode)->validate($cellValue)) { // @phan-suppress-current-line PhanParamTooMany
            return $countryCode === null
                ? "The value \"<c>{$cellValue}</c>\" has invalid phone number format."
                : "The value \"<c>{$cellValue}</c>\" has invalid phone number format for country \"{$countryCode}\".";
        }

        return null;
    }
}
