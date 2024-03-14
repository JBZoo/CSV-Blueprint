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

namespace JBZoo\CsvBlueprint\AggregateRules;

use MathPHP\Statistics\Average;

final class Median extends AbstarctAggregateRule
{
    public function validateRule(array $columnValues): ?string
    {
        if (\count($columnValues) === 0) {
            return null; // Cannot find the median of an empty list of numbers
        }

        $expMedian = $this->getOptionAsFloat();
        $median    = Average::median($columnValues);

        if ($expMedian !== $median) {
            return 'Column median is not equal to expected. ' .
                "Actual: <c>{$median}</c>, expected: <green>{$expMedian}</green>";
        }

        return null;
    }
}
