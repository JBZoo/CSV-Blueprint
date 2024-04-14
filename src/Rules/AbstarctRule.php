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

use JBZoo\CsvBlueprint\Rules\Cell\Exception;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ValidatorColumn;

use function JBZoo\Utils\bool;

abstract class AbstarctRule
{
    public const INPUT_TYPE = self::INPUT_TYPE_UNDEF;

    public const INPUT_TYPE_COUNTER = 0;
    public const INPUT_TYPE_INTS = 1;
    public const INPUT_TYPE_FLOATS = 2;
    public const INPUT_TYPE_STRINGS = 3;
    public const INPUT_TYPE_UNDEF = 4;

    // Modes
    public const DEFAULT = 'default';
    public const EQ = '';
    public const NOT = 'not';
    public const MIN = 'min';
    public const MAX = 'max';
    public const LESS = 'less';
    public const GREATER = 'greater';

    protected string $columnNameId;
    protected string $ruleCode;
    protected string $mode;

    private null|array|bool|float|int|string $options;

    abstract public function getHelpMeta(): array;

    public function __construct(
        string $columnNameId,
        null|array|bool|float|int|string $options,
        string $mode = self::DEFAULT,
    ) {
        $this->mode = $mode;
        $this->columnNameId = $columnNameId;
        $this->ruleCode = $this->getRuleCode();
        $this->options = $options;
        // TODO: Move resolving and validating expected value on this stage to make it only once (before validation).
    }

    public function validate(array|string $cellValue, int $line = ValidatorColumn::FALLBACK_LINE): ?Error
    {
        // TODO: Extract to abstract boolean cell/agregate rule
        if ($this->isEnabled($cellValue) === false) {
            return null;
        }

        if (\method_exists($this, 'validateRule')) { // TODO: Need to be removed
            try {
                /** @phan-suppress-next-line PhanUndeclaredMethod */
                $error = $this->validateRule($cellValue);
                if ($error !== null) {
                    return new Error($this->ruleCode, $error, $this->columnNameId, $line);
                }
            } catch (\Exception $e) {
                return new Error(
                    $this->ruleCode,
                    "Unexpected error: {$e->getMessage()}",
                    $this->columnNameId,
                    $line,
                );
            }
        } else {
            throw new Exception('Method "validateRule" not found in ' . static::class);
        }

        return null;
    }

    public function getHelp(): string
    {
        return (new DocBuilder($this))->getHelp();
    }

    public function getRuleCode(?string $mode = null): string
    {
        $mode ??= $this->mode;
        $postfix = $mode !== self::EQ && $mode !== self::DEFAULT ? "_{$mode}" : '';

        return Utils::camelToKebabCase((new \ReflectionClass($this))->getShortName()) . $postfix;
    }

    /**
     * @phan-suppress PhanPluginPossiblyStaticPublicMethod
     */
    public function getInputType(): int
    {
        return static::INPUT_TYPE;
    }

    public static function testValues(array $columnValues, null|array|bool|float|int|string $options = null): array|bool
    {
        foreach ($columnValues as $cellValue) {
            if (!static::testValue($cellValue, $options)) {
                return false;
            }
        }

        return true;
    }

    public static function testValue(string $cellValue, null|array|bool|float|int|string $options = null): bool
    {
        throw new Exception('Not implemented yet. Please override this method in the child class.');
    }

    protected function getOptionAsBool(): bool
    {
        // TODO: Replace to warning message
        if (!\is_bool($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be true|false.',
            );
        }

        return bool($this->options);
    }

    protected function getOptionAsString(): string
    {
        // TODO: Replace to warning message
        if (\is_array($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be int/float/string.',
            );
        }

        return (string)$this->options;
    }

    protected function getOptionAsInt(): int
    {
        // TODO: Replace to warning message
        if ($this->options === '' || !\is_numeric($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be integer.',
            );
        }

        return (int)$this->options;
    }

    protected function getOptionAsFloat(): float
    {
        // TODO: Replace to warning message
        if ($this->options === '' || !\is_numeric($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be integer/float.',
            );
        }

        return (float)$this->options;
    }

    protected function getOptionAsArray(): array
    {
        // TODO: Replace to warning message
        if (!\is_array($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be array of strings.',
            );
        }

        return $this->options;
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
}
