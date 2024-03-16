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

use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ColumnValidator;
use JBZoo\CsvBlueprint\Validators\Error;

use function JBZoo\Utils\bool;

abstract class AbstarctRule
{
    // Modes
    public const DEFAULT = 'default';
    public const EQ      = '';
    public const NOT     = 'not';
    public const MIN     = 'min';
    public const MAX     = 'max';

    protected const HELP_LEFT_PAD = 6;
    protected const HELP_DESC_PAD = 40;
    protected const HELP_TOP      = [];
    protected const HELP_OPTIONS  = [
        self::DEFAULT => ['FIXME', 'Add description.'],
        self::EQ      => ['5'],
        self::NOT     => ['4'],
        self::MIN     => ['1'],
        self::MAX     => ['10'],
    ];

    protected string $columnNameId;
    protected string $ruleCode;
    protected string $mode;

    private null|array|bool|float|int|string $options;

    public function __construct(
        string $columnNameId,
        null|array|bool|float|int|string $options,
        string $mode = self::DEFAULT,
    ) {
        $this->mode         = $mode;
        $this->columnNameId = $columnNameId;
        $this->ruleCode     = $this->getRuleCode();
        $this->options      = $options;
        // TODO: Move resolving and validating expected value on this stage to make it only once (before validation).
    }

    public function validate(array|string $cellValue, int $line = ColumnValidator::FALLBACK_LINE): ?Error
    {
        if ($this->isEnabled($cellValue) === false) {
            return null;
        }

        if (\method_exists($this, 'validateRule')) {
            /** @phan-suppress-next-line PhanUndeclaredMethod */
            $error = $this->validateRule($cellValue);
            if ($error !== null) {
                return new Error($this->ruleCode, $error, $this->columnNameId, $line);
            }
        } else {
            throw new \RuntimeException('Method "validateRule" not found in ' . static::class);
        }

        return null;
    }

    public function getHelp(): string
    {
        $leftPad = \str_repeat(' ', self::HELP_LEFT_PAD);

        $renderLine = function (array|string $row, string $mode) use ($leftPad): string {
            $ymlRuleCode = $this instanceof AbstractCombo ? $this->getComboRuleCode($mode) : $this->getRuleCode();
            $baseKeyVal  = "{$leftPad}{$ymlRuleCode}: {$row[0]}";

            if (isset($row[1])) {
                $desc = \rtrim($row[1], '.') . '.';

                return \str_pad($baseKeyVal, self::HELP_DESC_PAD, ' ', \STR_PAD_RIGHT) . "# {$desc}";
            }

            return $baseKeyVal;
        };

        $topComment = '';
        if (\count(static::HELP_TOP) > 0) {
            $topComment = "{$leftPad}# " . \implode("\n{$leftPad}# ", static::HELP_TOP);
        }

        if ($this instanceof AbstractCombo) {
            return \implode("\n", [
                $topComment,
                $renderLine(static::HELP_OPTIONS[self::EQ], self::EQ),
                $renderLine(static::HELP_OPTIONS[self::NOT], self::NOT),
                $renderLine(static::HELP_OPTIONS[self::MIN], self::MIN),
                $renderLine(static::HELP_OPTIONS[self::MAX], self::MAX),
            ]);
        }

        return \implode("\n", [
            $topComment,
            $renderLine(static::HELP_OPTIONS[self::DEFAULT] ?? self::HELP_OPTIONS[self::DEFAULT], self::DEFAULT),
        ]);
    }

    protected function getOptionAsBool(): bool
    {
        if (!\is_bool($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$this->getRuleCode()}\" rule. It should be true|false.",
            );
        }

        return bool($this->options);
    }

    protected function getOptionAsString(): string
    {
        if ($this->options === '' || \is_array($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$this->getRuleCode()}\" rule. It should be int/float/string.",
            );
        }

        return (string)$this->options;
    }

    protected function getOptionAsInt(): int
    {
        if ($this->options === '' || !\is_numeric($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$this->getRuleCode()}\" rule. It should be integer.",
            );
        }

        return (int)$this->options;
    }

    protected function getOptionAsFloat(): float
    {
        if ($this->options === '' || !\is_numeric($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$this->getRuleCode()}\" rule. It should be integer/float.",
            );
        }

        return (float)$this->options;
    }

    /**
     * @return string[]
     */
    protected function getOptionAsArray(): array
    {
        if (!\is_array($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$this->getRuleCode()}\" rule. It should be array of strings.",
            );
        }

        return (array)$this->options;
    }

    /**
     * Optimize performance
     * There is no need to validate empty values for predicates or if it's disabled.
     */
    protected function isEnabled(array|string $cellValue): bool
    {
        // List of rules that should be checked for empty values
        $exclusions = [
            'not_empty',
            'exact_value',
            'length_min',
        ];

        if (
            (\str_starts_with($this->ruleCode, 'is_') || \str_starts_with($this->ruleCode, 'ag:is_'))
            && !$this->getOptionAsBool()
        ) {
            return false;
        }

        if (\in_array($this->ruleCode, $exclusions, true)) {
            return true;
        }

        return $cellValue !== '';
    }

    protected function getRuleCode(): string
    {
        return Utils::camelToKebabCase((new \ReflectionClass($this))->getShortName());
    }
}
