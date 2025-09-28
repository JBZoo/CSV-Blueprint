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
use JBZoo\CsvBlueprint\Utils;
use MathPHP\Statistics\Descriptive;

final class ComboStddev extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'StdDev';

    public function getHelpMeta(): array
    {
        return [
            [
                'Standard deviation (For a sample; uses sample variance). It also known as SD or StdDev.',
                'StdDev is a measure that is used to quantify the amount '
                . 'of variation or dispersion of a set of data values.',
                ' - Low standard deviation indicates that the data points tend to be close to the mean '
                . '(also called the expected value) of the set.',
                ' - High standard deviation indicates that '
                . 'the data points are spread out over a wider range of values.',
                'See: https://en.wikipedia.org/wiki/Standard_deviation',
            ],
            [],
        ];
    }

    protected static function calcValue(array $columnValues, ?array $options = null): float|int|null
    {
        $columnValues = Utils::analyzeGuard($columnValues, self::INPUT_TYPE);
        if ($columnValues === null) {
            return null;
        }

        return Descriptive::standardDeviation($columnValues);
    }
}
