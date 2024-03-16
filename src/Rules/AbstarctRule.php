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

use function JBZoo\Data\json;
use function JBZoo\Utils\bool;

abstract class AbstarctRule
{
    protected const HELP = [];

    protected string $columnNameId;
    protected string $ruleCode;
    protected string $origRuleName;

    private null|array|bool|float|int|string $options;

    public function __construct(
        string $columnNameId,
        null|array|bool|float|int|string $options,
        string $origRuleName = '',
    ) {
        $this->columnNameId = $columnNameId;
        $this->origRuleName = $origRuleName;
        $this->ruleCode     = $this->getRuleCode();
        $this->options      = $options;
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

    protected function getOptionAsBool(): bool
    {
        if (\in_array(\strtolower($this->options), ['true', 'false'], true)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$origRuleName}\" rule. It should be true|false.",
            );
        }

        return bool($this->options);
    }

    protected function getOptionAsString(): string
    {
        if (\is_array($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$origRuleName}\" rule. It should be int/float/string.",
            );
        }

        return (string)$this->options;
    }

    protected function getOptionAsInt(): int
    {
        if (!\is_numeric($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$origRuleName}\" rule. It should be integer.",
            );
        }

        return (int)$this->options;
    }

    protected function getOptionAsFloat(): float
    {
        if (!\is_numeric($this->options)) {
            // TODO: Replace to warning message
            throw new Exception(
                "Invalid option \"{$this->options}\" for the \"{$origRuleName}\" rule. It should be integer/float.",
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
                "Invalid option \"{$this->options}\" for the \"{$origRuleName}\" rule. It should be array of strings.",
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
