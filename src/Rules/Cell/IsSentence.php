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

final class IsSentence extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Sentence with at least one space. Example: "Hello world!".',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        if (!self::testValue($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" should be a sentence";
        }

        return null;
    }

    public static function testValue(string $cellValue): bool
    {
        $shortLimit = 3;
        return \str_contains($cellValue, ' ')
            && \strlen($cellValue) > $shortLimit
            && \preg_match('/[a-z]/i', $cellValue);
    }
}
