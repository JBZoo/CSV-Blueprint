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
    public const EQ  = '';
    public const NOT = 'not';
    public const MIN = 'min';
    public const MAX = 'max';

    private string $mode;

    abstract protected function getCurrent(string $cellValue): float|int|string;

    abstract protected function getExpected(string $expectedValue): float|int|string;

    public function __construct(
        string $columnNameId,
        null|array|bool|float|int|string $options,
        string $origRuleName = '',
    ) {
        $this->mode = self::parseMode($origRuleName);
        parent::__construct($columnNameId, $options, $origRuleName);
    }

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

    /**
     * @param string|string[] $equel
     * @param string|string[] $min
     * @param string|string[] $max
     * @param string|string[] $not
     */
    public function getHelpCombo(
        array $equel = ['5'],
        array $not = ['4'],
        array $min = ['1'],
        array $max = ['10'],
    ): string {
        $leftPad = \str_repeat(' ', 6);
        $descPad = 40;

        $renderLine = function (array|string $row, string $mode) use ($leftPad, $descPad): string {
            $keyValue = $this->getComboRuleCode($mode);
            if (isset($row[1])) {
                $desc = \rtrim($row[1], '.') . '.';

                return \str_pad("{$leftPad}{$keyValue}: {$row[0]} ", $descPad, ' ', \STR_PAD_RIGHT) . "# {$desc}";
            }

            return "{$leftPad}{$keyValue}: {$row[0]}";
        };

        return \implode("\n", [
            "{$leftPad}# " . \implode("\n{$leftPad}# ", static::HELP),
            $renderLine($equel, self::EQ),
            $renderLine($not, self::NOT),
            $renderLine($min, self::MIN),
            $renderLine($max, self::MAX),
        ]);
    }

    public function test(string $cellValue, string $mode, bool $isHtml = false): string
    {
        $errorMessage = (string)$this->validateRuleCombo($cellValue, $mode);

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

    protected function getExpectedStr(string $expectedValue): string
    {
        return $expectedValue;
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

        if (static::NAME === '' || \count(static::HELP) === 0) {
            return null;
        }

        $params = $this->buildParams($cellValue);

        if ($params['check_option']($cellValue)) {
            return null;
        }

        if ($params['guard']($cellValue)) {
            return static::NAME . " rule is not applicable for the \"<c>{$cellValue}</c>\"";
        }

        if (!$params['comparator'][$mode]($params['expected'], $params['current'])) {
            return $params['message'](
                $cellValue,
                $params['verbs'][$mode],
                $params['current_str'],
                $params['expected_str'],
            );
        }

        return null;
    }

    private function buildParams(string $cellValue): array
    {
        return [
            'current'      => $this->getCurrent($cellValue),
            'current_str'  => $this->getCurrentStr($cellValue),
            'expected'     => $this->getExpected($this->getOptionAsString()),
            'expected_str' => $this->getExpectedStr($this->getOptionAsString()),

            'comparator' => [
                self::EQ  => static fn (float|int|string $exp, float|int|string $cur): bool => $exp === $cur,
                self::NOT => static fn (float|int|string $exp, float|int|string $cur): bool => $exp !== $cur,
                self::MIN => static fn (float|int|string $exp, float|int|string $cur): bool => $exp <= $cur,
                self::MAX => static fn (float|int|string $exp, float|int|string $cur): bool => $exp >= $cur,
            ],

            'check_option' => static fn (float|int|string $cellValue): bool => $cellValue === '',

            /** @phan-suppress PhanUnusedPublicNoOverrideMethodParameter */
            'guard' => static fn (float|int|string $cellValue): bool => false,

            'verbs' => [
                self::EQ  => 'not equal',
                self::NOT => 'equal',
                self::MIN => 'less',
                self::MAX => 'greater',
            ],

            'message' => function (
                string $cellValue,
                string $verb,
                string $currentStr,
                string $expectedStr,
            ): string {
                $prefix = '';
                if ($this->mode === self::NOT) {
                    $prefix = 'not ';
                }
                return 'The ' . static::NAME . " of the value \"<c>{$cellValue}</c>\" is {$currentStr}, " .
                    "which is {$verb} than the {$prefix}expected \"<green>{$expectedStr}</green>\"";
            },
        ];
    }
}
