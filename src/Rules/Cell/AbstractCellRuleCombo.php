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

namespace JBZoo\CsvBlueprint\Rules\Cell;

use JBZoo\CsvBlueprint\Rules\AbstarctRuleCombo;
use JBZoo\CsvBlueprint\Validators\ColumnValidator;
use JBZoo\CsvBlueprint\Validators\Error;

abstract class AbstractCellRuleCombo extends AbstarctRuleCombo
{
    abstract protected function getCurrent(string $cellValue): float;

    abstract protected function getExpected(): float;

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

    protected function getCurrentStr(string $cellValue): string
    {
        return (string)$this->getCurrent($cellValue);
    }

    protected function getExpectedStr(): string
    {
        return (string)$this->getExpected();
    }

    protected function getRuleCode(?string $mode = null): string
    {
        $mode ??= $this->mode;
        $postfix = $mode !== self::EQ ? "_{$mode}" : '';

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

    private static function compare(float $expected, float $actual, string $mode): bool
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
