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
use JBZoo\CsvBlueprint\Rules\AbstractRuleCombo;

abstract class AbstractAggregateRuleCombo extends AbstractRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_STRINGS;

    abstract protected static function calcValue(array $columnValues, ?array $options = null): null|float|int;

    abstract protected function getActualAggregate(array $colValues): ?float;

    public function getRuleCode(?string $mode = null): string
    {
        return 'ag:' . parent::getRuleCode($mode);
    }

    protected function getActual(array|string $value): float
    {
        if (\is_string($value)) {
            throw new Exception('The value should be an array of numbers/strings');
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
        $verb = static::VERBS[$mode];
        $name = static::NAME;

        try {
            $actual = $this->getActualAggregate($colValues); // Important to use the original method here!
        } catch (\Throwable $exception) {
            return "<red>{$exception->getMessage()}</red>"; // TODO: Expose the error/warning message in the report?
        }

        if ($actual === null) {
            return null; // Looks like it's impossible to calculate the aggregate value in this case. Skip.
        }

        try {
            $expected = $this->getExpected();
        } catch (\Throwable $exception) {
            return "<red>{$exception->getMessage()}</red>"; // TODO: Expose the error/warning message in the report?
        }

        if (!static::compare($expected, $actual, $mode)) {
            return "The {$name} in the column is \"<c>{$actual}</c>\", " .
                "which is {$verb} than the {$prefix}expected \"<green>{$expected}</green>\"";
        }

        return null;
    }
}
