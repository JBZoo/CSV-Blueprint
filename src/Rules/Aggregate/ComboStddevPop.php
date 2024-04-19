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

final class ComboStddevPop extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'standard deviation (SD+)';

    public function getHelpMeta(): array
    {
        return [
            [
                'SD+ (Standard deviation for a population; uses population variance)',
            ],
            [],
        ];
    }

    protected static function calcValue(array $columnValues, ?array $options = null): null|float|int
    {
        $columnValues = Utils::analyzeGuard($columnValues, self::INPUT_TYPE);
        if ($columnValues === null) {
            return null;
        }

        return Descriptive::standardDeviation($columnValues, Descriptive::POPULATION);
    }
}
