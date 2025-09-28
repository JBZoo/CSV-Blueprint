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
use MathPHP\Statistics\Average;

final class ComboRootMeanSquare extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'root mean square (quadratic mean)';

    public function getHelpMeta(): array
    {
        return [
            [
                'Root mean square (quadratic mean) '
                . 'The square root of the arithmetic mean of the squares of a set of numbers.',
                'See: https://en.wikipedia.org/wiki/Root_mean_square',
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

        return Average::rootMeanSquare($columnValues);
    }
}
