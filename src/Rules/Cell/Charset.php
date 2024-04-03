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

final class Charset extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            self::getHelpTitle(),
            [
                self::DEFAULT => [
                    'charset_code',
                    'Validates if a string is in a specific charset. Example: "UTF-8".',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $charset = $this->getOptionAsString();
        if ($charset === '') {
            return 'The charset is not specified.';
        }

        if (!Validator::charset($charset)->validate($cellValue)) {
            return "The value \"<c>{$cellValue}</c>\" is not in the charset \"{$charset}\".";
        }

        return null;
    }

    private static function getHelpTitle(): array
    {
        $maxOnLine = 10;
        $list = \mb_list_encodings();
        \sort($list, \SORT_NATURAL);
        $lines = \array_chunk($list, $maxOnLine);

        $result = ['Check if a string is in a specific charset. Available charsets:'];
        foreach ($lines as $line) {
            $result[] = ' - ' . \implode(', ', $line);
        }

        return $result;
    }
}
