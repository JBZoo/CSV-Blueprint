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

final class Unique extends AbstarctAggregateRule
{
    public function validateRule(array &$columnValues): ?string
    {
        if (!$this->getOptionAsBool()) {
            return null;
        }

        if (empty($columnValues)) {
            return null;
        }

        $uValuesCount = \count(\array_unique($columnValues));
        $valuesCount  = \count($columnValues);

        if ($uValuesCount !== $valuesCount) {
            return "Column has non-unique values. Total: {$valuesCount}, unique: {$uValuesCount}";
        }

        return null;
    }
}
