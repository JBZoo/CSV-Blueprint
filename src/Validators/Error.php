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

    /**
     * Returns a string representation of the error.
     * @return string the string representation of the object
     */
    public function __toString(): string
    {
        $columnStr = $this->getColumnName() === '' ? '' : ", column \"{$this->getColumnName()}\"";
        $error = \rtrim($this->getMessage(), '.');

        if ($this->line === self::UNDEFINED_LINE) {
            $fullMessage = "\"{$this->getRuleCode()}\"{$columnStr}. {$error}.";
        } else {
            $fullMessage = "\"{$this->getRuleCode()}\" at line <red>{$this->getLine()}</red>{$columnStr}. {$error}.";
        }

        return \str_replace('.</', '</', $fullMessage); // Double dots fix.
    }

    /**
     * Returns the rule code.
     * @return string the rule code
     */
    public function getRuleCode(): string
    {
        return $this->ruleCode;
    }

    /**
     * Returns the final error message.
     * @param  bool   $noTags if set to true, strips HTML tags from the message
     * @return string the error message, optionally without HTML tags
     */
    public function getMessage(bool $noTags = false): string
    {
        if ($noTags) {
            return \strip_tags(\rtrim($this->message));
        }

        return \rtrim($this->message, '.');
    }

    /**
     * Returns the column name associated with the error.
     * @return string the column name
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * Returns the line number of the error.
     * If the line number is undefined, it will return the string 'undef'.
     * @return int|string The line number of the error, or 'undef' if undefined
     */
    public function getLine(): int|string
    {
        return $this->line === self::UNDEFINED_LINE ? 'undef' : $this->line;
    }

    /**
     * Cleans the string representation of the object by removing any HTML tags.
     *
     * @return string the cleaned string representation of the object
     */
    public function toCleanString(): string
    {
        return \strip_tags((string)$this);
    }

    /**
     * Returns an array representation of the object.
     * Mostly for debugging purposes.
     */
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
