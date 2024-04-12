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

final class IsBic extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Validates a Bank Identifier Code (BIC) according to ISO 9362 standards. ' .
                    'See: https://en.wikipedia.org/wiki/ISO_9362',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        if (\preg_match('/^[a-z]{4}[a-z]{2}[a-z0-9]{2}([a-z0-9]{3})?$/i', $cellValue) === 0) { // NOSONAR
            return "The value \"<c>{$cellValue}</c>\" is not a valid BIC number (ISO 9362).";
        }

        return null;
    }
}
