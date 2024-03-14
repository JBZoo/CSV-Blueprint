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

namespace JBZoo\CsvBlueprint\CellRules;

final class Date extends AbstarctCellRule
{
    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }
        $expDate  = $this->getOptionAsDate();
        $cellDate = new \DateTimeImmutable($cellValue);

        if ($cellDate->getTimestamp() !== $expDate->getTimestamp()) {
            return "Value \"<c>{$cellValue}</c>\" is not equal to the expected date " .
                "\"<green>{$expDate->format(\DATE_RFC3339_EXTENDED)}</green>\"";
        }

        return null;
    }
}
