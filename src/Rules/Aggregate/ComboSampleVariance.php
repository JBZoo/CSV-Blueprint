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

namespace JBZoo\CsvBlueprint\Rules\Aggregate;

use JBZoo\CsvBlueprint\Rules\AbstractRule;
use MathPHP\Statistics\Descriptive;

final class ComboSampleVariance extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'population variance';

    public function getHelpMeta(): array
    {
        return [
            [
                'Unbiased sample variance Use when only a subset ' .
                'of all possible observations of the system are present.',
                'n - 1 degrees of freedom, where n is the number of observations.',
            ],
            [],
        ];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        return Descriptive::sampleVariance($columnValues);
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return Descriptive::sampleVariance($colValues);
    }
}
