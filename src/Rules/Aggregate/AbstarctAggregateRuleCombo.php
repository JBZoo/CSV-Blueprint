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

use JBZoo\CsvBlueprint\Rules\AbstarctRuleCombo;

abstract class AbstarctAggregateRuleCombo extends AbstarctRuleCombo
{
    abstract protected function getActualAggregate(array $colValues): float;

    protected function getActual(array|string $value): float
    {
        if (\is_string($value)) {
            throw new \InvalidArgumentException('The value should be an array of numbers/strings');
        }

        return $this->getActualAggregate($value);
    }

    protected function validateComboAggregate(array $colValues, ?string $mode = null): ?string
    {
        $prefix = $mode === self::NOT ? 'not ' : '';
        $verb   = self::VERBS[$mode];
        $name   = static::NAME;

        $actual   = $this->getActual($colValues);
        $expected = $this->getExpected();

        if (!self::compare($expected, $actual, $mode)) {
            return "The {$name} of the column is \"<c>{$actual}</c>\", " .
                "which is {$verb} than the {$prefix}expected \"<green>{$expected}</green>\"";
        }

        return null;
    }
}
