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

use JBZoo\CsvBlueprint\Rules\AbstarctRule;

abstract class AbstarctCellRule extends AbstarctRule
{
    /**
     * Validate the rule.
     *
     * This method takes a string $cellValue as input and returns a nullable string.
     */
    abstract public function validateRule(string $cellValue): ?string;

    /**
     * @param string|string[] $equel
     * @param string|string[] $min
     * @param string|string[] $max
     * @param string|string[] $not
     */
    public function getHelpLine(array $value = []): string
    {
        $keyValue = $this->getRuleCode();
        if (isset($value[1])) {
            $desc = \rtrim($value[1], '.') . '.';

            return \str_pad("{$leftPad}{$keyValue}: {$value[0]} ", $descPad, ' ', \STR_PAD_RIGHT) . "# {$desc}";
        }

        return "{$leftPad}{$keyValue}: {$value[0]}";
    }
}
