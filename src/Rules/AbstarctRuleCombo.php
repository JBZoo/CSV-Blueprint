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

namespace JBZoo\CsvBlueprint\Rules;

use JBZoo\CsvBlueprint\Rules\Aggregate\AbstarctAggregateRuleCombo;
use JBZoo\CsvBlueprint\Rules\Cell\AbstractCellRuleCombo;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ValidatorColumn;

abstract class AbstarctRuleCombo extends AbstarctRule
{
    protected const NAME = 'UNDEFINED';

    protected const VERBS = [
        self::MIN     => 'less',
        self::GREATER => 'less and not equal',
        self::NOT     => 'equal',
        self::EQ      => 'not equal',
        self::LESS    => 'greater and not equal',
        self::MAX     => 'greater',
    ];

    abstract protected function getExpected(): float;

    abstract protected function getActual(array|string $value): float;

    public function validate(array|string $cellValue, int $line = ValidatorColumn::FALLBACK_LINE): ?Error
    {
        $error = $this->validateCombo($cellValue);

        if ($error !== null) {
            return new Error($this->ruleCode, $error, $this->columnNameId, $line);
        }

        return null;
    }

    public function test(array|string $cellValue, bool $isHtml = false): string
    {
        $errorMessage = (string)$this->validateCombo($cellValue);

        return $isHtml ? $errorMessage : \strip_tags($errorMessage);
    }

    public function getRuleCode(?string $mode = null): string
    {
        return \str_replace('combo_', '', parent::getRuleCode($mode));
    }

    public static function parseMode(string $origRuleName): string
    {
        $postfixes = [self::MIN, self::GREATER, self::NOT, self::LESS, self::MAX];

        if (\preg_match('/_(' . \implode('|', $postfixes) . ')$/', $origRuleName, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    protected static function compare(float $expected, float $actual, string $mode): bool
    {
        // Rounding numbers to 10 decimal places before strict comparison is necessary due to the inherent
        // imprecision of floating-point arithmetic. Computers represent floating-point numbers in binary,
        // which can lead to small rounding errors for what we expect to be precise decimal values.
        // As a result, direct comparisons of floating-point numbers that should be equal might fail.
        // Rounding both numbers to a fixed number of decimal places before comparison can mitigate this issue,
        // making it a practical approach to handle the imprecision and ensure more reliable equality checks.
        // Since PHP's default precision is 12 digits, we chose 10 digits to be more confident.
        $precision = 10;
        $expected  = \round($expected, $precision);
        $actual    = \round($actual, $precision);

        return match ($mode) {
            self::MIN     => $expected <= $actual,
            self::GREATER => $expected < $actual,
            self::NOT     => $expected !== $actual,
            self::EQ      => $expected === $actual,
            self::LESS    => $expected > $actual,
            self::MAX     => $expected >= $actual,
            default       => throw new \InvalidArgumentException("Unknown mode: {$mode}"),
        };
    }

    private function validateCombo(array|string $cellValue): ?string
    {
        if ($this instanceof AbstractCellRuleCombo) {
            if (!\is_string($cellValue)) {
                throw new \InvalidArgumentException('The value should be a string');
            }

            return $this->validateComboCell($cellValue, $this->mode);
        }

        if ($this instanceof AbstarctAggregateRuleCombo) {
            if (!\is_array($cellValue)) {
                throw new \InvalidArgumentException('The value should be an array of numbers/strings');
            }

            return $this->validateComboAggregate($cellValue, $this->mode);
        }

        throw new \LogicException('Unknown rule type: ' . static::class);
    }
}
