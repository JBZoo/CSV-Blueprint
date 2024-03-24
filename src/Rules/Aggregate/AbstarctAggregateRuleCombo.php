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

use JBZoo\CsvBlueprint\Rules\AbstarctRule;
use JBZoo\CsvBlueprint\Rules\AbstarctRuleCombo;

abstract class AbstarctAggregateRuleCombo extends AbstarctRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_STRINGS;

    protected const HELP_OPTIONS = [
        self::EQ  => ['5.123', ''],
        self::NOT => ['4.123', ''],
        self::MIN => ['1.123', ''],
        self::MAX => ['10.123', ''],
    ];

    abstract protected function getActualAggregate(array $colValues): ?float;

    public function getRuleCode(?string $mode = null): string
    {
        return 'ag:' . parent::getRuleCode($mode);
    }

    protected function getActual(array|string $value): float
    {
        if (\is_string($value)) {
            throw new \InvalidArgumentException('The value should be an array of numbers/strings');
        }

        $result = $this->getActualAggregate($value);

        return $result ?? 0;
    }

    protected function getExpected(): float
    {
        return $this->getOptionAsFloat();
    }

    protected function validateComboAggregate(array $colValues, string $mode): ?string
    {
        $prefix = $mode === self::NOT ? 'not ' : '';
        $verb   = static::VERBS[$mode];
        $name   = static::NAME;

        try {
            $actual = $this->getActualAggregate($colValues); // Important to use the original method here!
        } catch (\Throwable) {
            $actual = null; // TODO: Expose the error/warning message in the report?
        }

        if ($actual === null) {
            return null; // Looks like it's impossible to calculate the aggregate value in this case. Skip.
        }

        $expected = $this->getExpected();

        if (!self::compare($expected, $actual, $mode)) {
            return "The {$name} in the column is \"<c>{$actual}</c>\", " .
                "which is {$verb} than the {$prefix}expected \"<green>{$expected}</green>\"";
        }

        return null;
    }

    protected static function stringsToFloat(array $colValues): array
    {
        return \array_map('\JBZoo\Utils\float', $colValues);
    }
}
