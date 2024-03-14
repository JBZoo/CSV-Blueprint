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

final class CountMax extends AbstarctAggregateRule
{
    public function validateRule(array $columnValues): ?string
    {
        $expMax = $this->getOptionAsInt();
        $count  = \count($columnValues);

        if ($expMax < $count) {
            return 'Column count is greater than expected. ' .
                "Actual: <c>{$count}</c>, expected: <green>{$expMax}</green>";
        }

        return null;
    }
}
