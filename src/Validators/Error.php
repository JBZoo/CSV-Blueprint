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

namespace JBZoo\CsvBlueprint\Validators;

final class Error
{
    public const UNDEFINED_LINE = 0;

    public function __construct(
        private string $ruleCode,
        private string $message,
        private string $columnName = '',
        private int $line = self::UNDEFINED_LINE,
    ) {
    }

    public function __toString(): string
    {
        $columnStr = $this->getColumnName() === '' ? '' : ", column \"{$this->getColumnName()}\"";

        if ($this->line === self::UNDEFINED_LINE) {
            return "\"{$this->getRuleCode()}\"{$columnStr}. {$this->getMessage()}.";
        }

        return "\"{$this->getRuleCode()}\" at line <red>{$this->getLine()}</red>{$columnStr}. {$this->getMessage()}.";
    }

    public function getRuleCode(): string
    {
        return $this->ruleCode;
    }

    public function getMessage(bool $noTags = false): string
    {
        if ($noTags) {
            return \strip_tags(\rtrim($this->message));
        }

        return \rtrim($this->message, '.');
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getLine(): int|string
    {
        return $this->line === self::UNDEFINED_LINE ? 'undef' : $this->line;
    }

    public function toCleanString(): string
    {
        return \strip_tags((string)$this);
    }

    public function toArray(): array
    {
        return [
            'ruleCode'   => $this->ruleCode,
            'message'    => $this->message,
            'columnName' => $this->columnName,
            'line'       => $this->line,
        ];
    }
}
