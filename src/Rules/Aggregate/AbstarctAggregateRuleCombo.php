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
    protected function validateComboAggregate(array $columnValues, ?string $mode = null): ?string
    {
        $prefix = $mode === self::NOT ? 'not ' : '';
        $verb   = self::VERBS[$mode];
        $name   = static::NAME;

        $currentStr  = $this->getActuallAggregateStr($columnValues);
        $expectedStr = $this->getExpectedStr();

        if ($currentStr !== '') {
            $currentStr = " is {$currentStr}";
        }

        if ($cellValue === '') {
            return null;
        }

        if (!self::compare($this->getExpected(), $this->getActual($columnValues), $mode)) {
            return "The {$name} of the value \"<c>{$cellValue}</c>\"{$currentStr}, " .
                "which is {$verb} than the {$prefix}expected \"<green>{$expectedStr}</green>\"";
        }

        return null;
    }

}
