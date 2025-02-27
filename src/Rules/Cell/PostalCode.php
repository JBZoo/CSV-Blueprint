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

final class PostalCode extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'US',
                    'Validate postal code by country code (alpha-2). '
                    . 'Example: "02179". Extracted from https://www.geonames.org',
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

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $countryCodes = self::getPostalCountryCodes();

        $countByRegex = [];

        foreach ($columnValues as $value) {
            if (!IsDate::testValue($value)) {
                return false;
            }

            foreach ($countryCodes as $countryCode) {
                if (Validator::postalCode($countryCode)->validate($value)) {
                    $countByRegex[$countryCode] = ($countByRegex[$countryCode] ?? 0) + 1;
                }
            }
        }

        $originalCount = \count(\array_filter($columnValues, static fn (string $value) => $value !== ''));
        $validFormats = \array_keys(\array_filter($countByRegex, static fn (int $count) => $count === $originalCount));

        if (\count($validFormats) > 0) {
            return (string)\reset($validFormats);
        }

        return false;
    }

    private static function getPostalCountryCodes(): array
    {
        return \explode(
            '|',
            'KY|AD|AL|AM|AR|AS|AT|AU|AX|AZ|BA|BB|BD|BE|BG|BH|BL|BM|BN|BR|BY|CA|CH|CL|CN|CO|CR|CS|CU|CV|CX|CY|'
            . 'CZ|DE|DK|DO|DZ|EC|EE|EG|ES|ET|FI|FM|FO|FR|GB|GE|GF|GG|GL|GP|GR|GT|GU|GW|HN|HR|HT|HU|ID|IE|IL|IM|'
            . 'IN|IQ|IR|IS|IT|JE|JO|JP|KE|KG|KH|KP|KR|KW|KZ|LA|LB|LI|LK|LR|LS|LT|LU|LV|MA|MC|MD|ME|MF|MG|MH|MK|'
            . 'MM|MN|MP|MQ|MT|MV|MW|MX|MY|MZ|NC|NE|NF|NG|NI|NL|NO|NP|NZ|OM|PE|PF|PG|PH|PK|PL|PM|PR|PT|PW|PY|RE|'
            . 'RO|RS|RU|SA|SD|SE|SG|SH|SI|SJ|SK|SM|SN|SO|SV|SZ|TC|TH|TJ|TM|TN|TR|TW|UA|US|UY|UZ|VA|VE|VI|VN|WF|'
            . 'YT|ZA|ZM',
        );
    }
}
