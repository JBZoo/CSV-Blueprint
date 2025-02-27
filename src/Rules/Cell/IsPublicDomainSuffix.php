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

final class IsPublicDomainSuffix extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'The input is a public ICANN domain suffix. Example: "com", "nom.br", "net" etc.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!self::testValue($cellValue)) {
            return "The value \"<c>{$cellValue}</c>\" is not a valid public domain suffix. "
                . 'Example: "com", "nom.br", "net" etc.';
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        // @phpstan-ignore-next-line
        return Validator::oneOf(Validator::tld(), Validator::publicDomainSuffix())->validate($cellValue);
    }
}
