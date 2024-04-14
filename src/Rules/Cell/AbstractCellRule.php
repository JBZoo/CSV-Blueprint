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

use JBZoo\CsvBlueprint\Rules\AbstractRule;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractCellRule extends AbstractRule
{
    /**
     * Test input with a rule.
     * @param  string      $cellValue the cell value to be validated
     * @return null|string the error message if the rule validation fails, null otherwise
     */
    abstract public function validateRule(string $cellValue): ?string;

    /**
     * The metod for Unit tests only! Let's say it's a lifehack :).
     * @param  string $cellValue the cell value to be tested
     * @param  bool   $isHtml    flag to specify whether the error message should be returned as HTML or plain text
     * @return string The error message. If $isHtml is true, the error message will contain HTML tags, otherwise it
     *                will be plain text.
     */
    public function test(string $cellValue, bool $isHtml = false): string
    {
        $errorMessage = (string)$this->validateRule($cellValue);
        return $isHtml ? $errorMessage : \strip_tags($errorMessage);
    }
}
