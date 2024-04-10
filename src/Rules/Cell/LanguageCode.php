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

use JBZoo\CsvBlueprint\Utils;
use Respect\Validation\Rules\LanguageCode as RespectLanguageCode;
use Respect\Validation\Validator;

class LanguageCode extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [
                'Validates whether the input is language code based on ISO 639.',
                'Available options: "alpha-2" (Ex: "en"), "alpha-3" (Ex: "eng").',
                'See: https://en.wikipedia.org/wiki/ISO_639.',
            ],
            [
                self::DEFAULT => ['alpha-2', 'Examples: "en", "eng"'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $validSets = [RespectLanguageCode::ALPHA2, RespectLanguageCode::ALPHA3];

        $set = $this->getOptionAsString();

        if (!\in_array($set, $validSets, true)) {
            return "Unknown language set: \"<c>{$set}</c>\". " .
                'Available options: ' . Utils::printList($validSets, 'green');
        }

        if (!Validator::languageCode($set)->validate($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid \"{$set}\" language code.";
        }

        return null;
    }
}
