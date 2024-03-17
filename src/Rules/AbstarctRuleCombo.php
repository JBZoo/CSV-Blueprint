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
use JBZoo\CsvBlueprint\Validators\ColumnValidator;
use JBZoo\CsvBlueprint\Validators\Error;

abstract class AbstarctRuleCombo extends AbstarctRule
{
    protected const NAME = 'UNDEFINED';

    protected const VERBS = [
        self::EQ  => 'not equal',
        self::NOT => 'equal',
        self::MIN => 'less',
        self::MAX => 'greater',
    ];

    abstract protected function getExpected(): float;

    abstract protected function getActual(array|string $value): float;

    public function validate(array|string $cellValue, int $line = ColumnValidator::FALLBACK_LINE): ?Error
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

    public static function parseMode(string $origRuleName): string
    {
        $postfixes = [self::MAX, self::MIN, self::NOT];

        if (\preg_match('/_(' . \implode('|', $postfixes) . ')$/', $origRuleName, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    protected function getRuleCode(?string $mode = null): string
    {
        return \str_replace('combo_', '', parent::getRuleCode($mode));
    }

    protected static function compare(float $expected, float $actual, string $mode): bool
    {
        return match ($mode) {
            self::EQ  => $expected === $actual,
            self::NOT => $expected !== $actual,
            self::MIN => $expected <= $actual,
            self::MAX => $expected >= $actual,
            default   => throw new \InvalidArgumentException("Unknown mode: {$mode}"),
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
