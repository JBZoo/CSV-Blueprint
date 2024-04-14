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

use JBZoo\CsvBlueprint\Rules\AbstractRuleCombo;

abstract class AbstractCellRuleCombo extends AbstractRuleCombo
{
    abstract protected function getActualCell(string $cellValue): float;

    protected function getActual(array|string $value): float
    {
        if (\is_array($value)) {
            throw new Exception('This method should not be called with an array');
        }

        return $this->getActualCell($value);
    }

    protected function getCurrentStr(string $cellValue): string
    {
        return (string)$this->getActualCell($cellValue);
    }

    protected function getExpectedStr(): string
    {
        return (string)$this->getExpected();
    }

    protected function validateComboCell(string $cellValue, ?string $mode = null): ?string
    {
        $mode ??= $this->mode;

        if ($cellValue === '') {
            return null;
        }

        if (!static::compare($this->getExpected(), $this->getActual($cellValue), $mode)) {
            return $this->getErrorMessage($cellValue, $mode);
        }

        return null;
    }

    private function getErrorMessage(string $cellValue, string $mode): string
    {
        $prefix = $mode === self::NOT ? 'not ' : '';
        $verb = self::VERBS[$mode];
        $name = static::NAME;

        $currentStr = $this->getCurrentStr($cellValue);
        $expectedStr = $this->getExpectedStr();

        if ($currentStr !== '') {
            $currentStr = " is {$currentStr}";
        }

        if ($name === '') {
            return "The value \"<c>{$cellValue}</c>\"{$currentStr} " .
                "is {$verb} than the {$prefix}expected \"<green>{$expectedStr}</green>\"";
        }

        return "The {$name} of the value \"<c>{$cellValue}</c>\"{$currentStr}, " .
            "which is {$verb} than the {$prefix}expected \"<green>{$expectedStr}</green>\"";
    }
}
