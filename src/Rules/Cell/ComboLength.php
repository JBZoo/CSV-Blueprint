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

final class ComboLength extends AbstractCellRuleCombo
{
    protected const NAME = 'length';

    public function getHelpMeta(): array
    {
        return [['Checks length of a string including spaces (multibyte safe).'], []];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $min = null;
        $max = null;

        foreach ($columnValues as $cellValue) {
            $length = \mb_strlen($cellValue);

            if ($min === null || $length < $min) {
                $min = $length;
            }

            if ($max === null || $length > $max) {
                $max = $length;
            }
        }

        return $max === $min
            ? ['' => $max]
            : ['min' => $min, 'max' => $max];
    }

    protected function getExpected(): float
    {
        return $this->getOptionAsInt();
    }

    protected function getActualCell(string $cellValue): float
    {
        return \mb_strlen($cellValue);
    }
}
