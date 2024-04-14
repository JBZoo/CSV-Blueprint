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

namespace JBZoo\CsvBlueprint\Rules\Aggregate;

use JBZoo\CsvBlueprint\Rules\AbstractRule;

abstract class AbstractAggregateRule extends AbstractRule
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_STRINGS;

    /**
     * Validates a rule based on the given column values.
     * @param  string[]    $columnValues The column values to validate against the rule
     * @return null|string The error message if the rule is not valid, null otherwise
     */
    abstract public function validateRule(array $columnValues): ?string;

    /**
     * The metod for Unit tests only! Let's say it's a lifehack :).
     * @param  array  $cellValue The input array for testing
     * @param  bool   $isHtml    Whether to return the error message as HTML or plain text
     * @return string The error message, either as HTML or plain text, based on the value of $isHtml
     */
    public function test(array $cellValue, bool $isHtml = false): string
    {
        $errorMessage = (string)$this->validateRule($cellValue);
        return $isHtml ? $errorMessage : \strip_tags($errorMessage);
    }

    /**
     * Get rule code method.
     * @param  null|string $mode (optional) The mode for getting the rule code (default: null)
     * @return string      The rule code with prefix 'ag:'
     */
    public function getRuleCode(?string $mode = null): string
    {
        return 'ag:' . parent::getRuleCode($mode);
    }
}
