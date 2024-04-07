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

use Respect\Validation\Rules\CreditCard as RespectCreditCard;
use Respect\Validation\Validator;

final class CreditCard extends AbstractCellRule
{
    private const BRANDS = [
        RespectCreditCard::ANY,
        RespectCreditCard::AMERICAN_EXPRESS,
        RespectCreditCard::DINERS_CLUB,
        RespectCreditCard::DISCOVER,
        RespectCreditCard::JCB,
        RespectCreditCard::MASTERCARD,
        RespectCreditCard::VISA,
        RespectCreditCard::RUPAY,
    ];

    public function getHelpMeta(): array
    {
        return [
            [
                'Validates whether the input is a credit card number.',
                'Available credit card brands: "' . \implode('", "', self::BRANDS) . '".',
            ],
            [self::DEFAULT => [RespectCreditCard::ANY, 'Example: "5376-7473-9720-8720"']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $brand = $this->getOptionAsString();
        if ($brand === '') {
            return 'The brand is required. Example: "Any", "Visa", "MasterCard"';
        }

        if (!Validator::creditCard($brand)->validate($cellValue)) {
            return "The value \"<c>{$cellValue}</c>\" has invalid credit card format for brand \"{$brand}\".";
        }

        return null;
    }
}
