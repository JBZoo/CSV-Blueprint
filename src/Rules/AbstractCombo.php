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

abstract class AbstractCombo extends AbstarctCellRule
{
    public const EQ  = '';
    public const NOT = 'not';
    public const MIN = 'min';
    public const MAX = 'max';

    protected string $name = '';
    protected string $help = '';

    abstract protected function getExpected(string $cellValue): float|int|string;

    abstract protected function getCurrent(string $cellValue): float|int|string;

    public function validateComboRule(string $cellValue, string $mode): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        if ($this->name === '' || $this->help === '') {
            return null;
        }

        $params = $this->buildParams($cellValue, $mode);

        if (!$params['check_option']($cellValue)) {
            return null;
        }

        if (!$params['guard']($cellValue)) {
            return "{$params['name']} rule is not applicable for the \"<c>{$cellValue}</c>\"";
        }

        if (!$params['comparator'][$mode]($params['expected'], $params['current'])) {
            return $params['message'](
                $cellValue,
                $params['name'],
                $params['name_prefix'],
                $params['verbs'][$mode],
                $params['current_str'],
                $params['expected_str'],
            );
        }

        return null;
    }

    public function validateRule(string $cellValue): ?string
    {
        $parts   = \explode('_', $this->ruleCode);
        $postfix = \end($parts);

        return $this->validateComboRule($cellValue, $this->modeMap['prefix'][$postfix] ?? self::EQ);
    }

    public function getHelp(): array
    {
        return [
            '# ' . $this->help,
            $this->getRuleCode() . ': 5',
            $this->getRuleCode(self::MIN) . ': 1',
            $this->getRuleCode(self::MAX) . ': 10',
            $this->getRuleCode(self::NOT) . ': 42',
        ];
    }

    protected function getExpectedStr(string $cellValue, string $mode): string
    {
        $prefix = '';
        if ($mode === self::NOT) {
            $prefix = 'not ';
        }

        return "{$prefix}expected \"<green>{$this->getExpected($cellValue)}</green>\"";
    }

    protected function getCurrentStr(string $cellValue): string
    {
        return (string)$this->getCurrent($cellValue);
    }

    protected function getNamePrefix(string $mode): string
    {
        if ($mode === self::MIN) {
            return 'minimal ';
        }

        if ($mode === self::MAX) {
            return 'maximal ';
        }

        return '';
    }

    protected function buildParams(string $cellValue, string $mode): array
    {
        return [
            'name'        => $this->name,
            'description' => $this->help,

            'name_prefix' => $this->getNamePrefix($mode),

            'current'      => $this->getCurrent($cellValue),
            'current_str'  => $this->getCurrentStr($cellValue),
            'expected'     => $this->getExpected($cellValue),
            'expected_str' => $this->getExpectedStr($cellValue, $mode),

            'comparator' => [
                self::EQ => static fn (
                    float|int|string $expected,
                    float|int|string $current,
                ): bool => $expected === $current,
                self::NOT => static fn (
                    float|int|string $expected,
                    float|int|string $current,
                ): bool => $expected !== $current,
                self::MIN => static fn (
                    float|int|string $expected,
                    float|int|string $current,
                ): bool => $expected <= $current,
                self::MAX => static fn (
                    float|int|string $expected,
                    float|int|string $current,
                ): bool => $expected >= $current,
            ],

            'check_option' => static fn (float|int|string $cellValue): bool => true,
            'guard'        => static fn (float|int|string $cellValue): bool => true,

            'verbs' => [
                self::EQ  => 'not equal',
                self::NOT => 'equal',
                self::MIN => 'less',
                self::MAX => 'greater',
            ],

            'message' => static fn (
                string $cellValue,
                string $name,
                string $namePrefix,
                string $verb,
                string $currentStr,
                string $expectedStr,
            ): string => "The {$name} of the \"<c>{$cellValue}</c>\" is {$currentStr}, " .
                "which is {$verb} than the {$expectedStr}",
        ];
    }

    protected function getRuleCode(?string $mode = null): string
    {
        $postfix = '';
        if ($mode !== self::EQ) {
            $postfix = $mode ? "_{$mode}" : '';
        }

        return \str_replace('combo_', '', parent::getRuleCode($mode)) . $postfix;
    }
}
