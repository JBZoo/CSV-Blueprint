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

namespace JBZoo\CsvBlueprint\Validators\Rules;

final class MinDate extends AbstarctRule
{
    public function validateRule(?string $cellValue): ?string
    {
        $minDate  = $this->getOptionAsDate();
        $cellDate = new \DateTimeImmutable($cellValue);

        if ($cellDate < $minDate) {
            return "Value \"{$cellValue}\" is less than the minimum " .
                "date \"{$minDate->format(\DATE_RFC3339_EXTENDED)}\"";
        }

        return null;
    }
}
