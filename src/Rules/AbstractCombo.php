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

use JBZoo\CsvBlueprint\Rules\Cell\AbstarctCellRule;
use JBZoo\CsvBlueprint\Validators\ColumnValidator;
use JBZoo\CsvBlueprint\Validators\Error;

abstract class AbstractCombo extends AbstarctCellRule
{
    protected const NAME = 'UNDEFINED';

    private const VERBS = [
        self::EQ  => 'not equal',
        self::NOT => 'equal',
        self::MIN => 'less',
        self::MAX => 'greater',
    ];

    abstract protected function getCurrent(string $cellValue): float|int|string;

    abstract protected function getExpected(): float|int|string;

    public function validateRule(string $cellValue): ?string
    {
        return $this->validateRuleCombo($cellValue, $this->mode);
    }

    public function validate(array|string $cellValue, int $line = ColumnValidator::FALLBACK_LINE): ?Error
    {
        if (\is_array($cellValue)) {
            return null; // TODO: Add support for array values for aggregate rules
        }

        $error = $this->validateRule($cellValue);
        if ($error !== null) {
            return new Error($this->ruleCode, $error, $this->columnNameId, $line);
        }

        return null;
    }

    public function test(string $cellValue, bool $isHtml = false): string
    {
        $errorMessage = (string)$this->validateRuleCombo($cellValue, $this->mode);

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

    protected function getCurrentStr(string $cellValue): string
    {
        return (string)$this->getCurrent($cellValue);
    }

    protected function getExpectedStr(): string
    {
        return (string)$this->getExpected();
    }

    protected function getComboRuleCode(?string $mode = null): string
    {
        $postfix = '';
        if ($mode !== self::EQ) {
            $postfix = "_{$mode}";
        }

        return \str_replace('combo_', '', parent::getRuleCode()) . $postfix;
    }

    protected function getRuleCode(): string
    {
        $postfix = $this->mode !== self::EQ ? "_{$this->mode}" : '';

        return \str_replace('combo_', '', parent::getRuleCode()) . $postfix;
    }

    private function validateRuleCombo(string $cellValue, ?string $mode = null): ?string
    {
        $mode ??= $this->mode;

        if ($cellValue === '') {
            return null;
        }

        if (!self::compare($this->getExpected(), $this->getCurrent($cellValue), $mode)) {
            return $this->getErrorMessage($cellValue, $mode);
        }

        return null;
    }

    private function getErrorMessage(string $cellValue, string $mode): string
    {
        $prefix = $mode === self::NOT ? 'not ' : '';
        $verb   = self::VERBS[$mode];
        $name   = static::NAME;

        $currentStr  = $this->getCurrentStr($cellValue);
        $expectedStr = $this->getExpectedStr();

        if ($currentStr !== '') {
            $currentStr = " is {$currentStr}";
        }

        return "The {$name} of the value \"<c>{$cellValue}</c>\"{$currentStr}, " .
            "which is {$verb} than the {$prefix}expected \"<green>{$expectedStr}</green>\"";
    }

    private static function compare(float|int|string $expected, float|int|string $actual, string $mode): bool
    {
        return match ($mode) {
            self::EQ  => $expected === $actual,
            self::NOT => $expected !== $actual,
            self::MIN => $expected <= $actual,
            self::MAX => $expected >= $actual,
            default   => throw new \InvalidArgumentException("Unknown mode: {$mode}"),
        };
    }
}
