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

final class Contains extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['World', 'Example: "Hello World!". The string must contain "World" in any place.']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $expected = $this->getOptionAsString();

        if ($expected === '') {
            return 'Rule must contain at least one char in schema file.';
        }

        if (\strpos($cellValue, $expected) === false) {
            return "Value \"<c>{$cellValue}</c>\" must contain \"<green>{$expected}</green>\"";
        }

        return null;
    }
}
