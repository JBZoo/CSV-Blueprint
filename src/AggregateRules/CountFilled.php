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

final class CountFilled extends AbstarctAggregateRule
{
    public function validateRule(array $columnValues): ?string
    {
        $expCountFilled   = $this->getOptionAsInt();
        $countFilledLines = \count(\array_filter($columnValues, static fn ($value) => $value !== ''));

        if ($expCountFilled !== $countFilledLines) {
            return 'Column count of filled lines is not equal to expected. ' .
                "Actual: <c>{$countFilledLines}</c>, expected: <green>{$expCountFilled}</green>";
        }

        return null;
    }
}
