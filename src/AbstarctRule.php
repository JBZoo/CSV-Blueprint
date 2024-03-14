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

namespace JBZoo\CsvBlueprint;

use JBZoo\CsvBlueprint\AggregateRules\AbstarctAggregateRule;
use JBZoo\CsvBlueprint\Validators\ColumnValidator;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\Data\Data;

use function JBZoo\Data\data;
use function JBZoo\Data\json;
use function JBZoo\Utils\bool;
use function JBZoo\Utils\float;
use function JBZoo\Utils\int;

abstract class AbstarctRule
{
    protected null|array|bool|float|int|string $options;

    private string $columnNameId;
    private string $ruleCode;

    public function __construct(string $columnNameId, null|array|bool|float|int|string $options = null)
    {
        $this->columnNameId = $columnNameId;
        $this->options      = $options;
        $this->ruleCode     = $this->getRuleCode();
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
        return bool($this->options);
    }

    protected function getOptionAsString(): string
    {
        if (\is_array($this->options)) {
            return (string)json($this->options);
        }

        return (string)$this->options;
    }

    protected function getOptionAsInt(): int
    {
        return int($this->options);
    }

    protected function getOptionAsFloat(): float
    {
        return float($this->options);
    }

    protected function getOptionAsArray(): array
    {
        return (array)$this->options;
    }

    protected function getOptionAsData(): Data
    {
        return data($this->options);
    }

    protected function getOptionAsDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->getOptionAsString(), new \DateTimeZone('UTC'));
    }

    /**
     * Optimize performance
     * There is no need to validate empty values for predicates or if it's disabled.
     */
    protected function isEnabled(array|string $cellValue): bool
    {
        return !(
            \is_string($cellValue)                              // Only for CellRules
            && \str_starts_with($this->ruleCode, 'is_')         // It's probably predicate
            && (!$this->getOptionAsBool() || $cellValue === '') // Check is it enabled
        );
    }

    private function getRuleCode(): string
    {
        $prefix = '';
        if ($this instanceof AbstarctAggregateRule) {
            $prefix = 'ag:';
        }

        return $prefix . Utils::camelToKebabCase((new \ReflectionClass($this))->getShortName());
    }
}
