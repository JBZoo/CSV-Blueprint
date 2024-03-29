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

use JBZoo\CsvBlueprint\Rules\AbstarctRule;

abstract class AbstractAggregateRule extends AbstarctRule
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_STRINGS;

    /**
     * Validate the rule.
     *
     * TODO: This method takes an array reference &$columnValues as input and returns a nullable string.
     * We use a reference to the array to avoid copying the array. Important memory optimization!
     * Please DO NOT change the array in this method!
     *
     * @param string[] $columnValues
     */
    abstract public function validateRule(array $columnValues): ?string;

    public function test(array $cellValue, bool $isHtml = false): string
    {
        $errorMessage = (string)$this->validateRule($cellValue);

        return $isHtml ? $errorMessage : \strip_tags($errorMessage);
    }

    public function getRuleCode(?string $mode = null): string
    {
        return 'ag:' . parent::getRuleCode($mode);
    }
}
