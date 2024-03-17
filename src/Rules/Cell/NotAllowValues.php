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

class NotAllowValues extends AbstractCellRule
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => ['[ invalid ]', 'Strict set of values that are NOT allowed.'],
    ];

    public function validateRule(string $cellValue): ?string
    {
        $notAllowedValues = $this->getOptionAsArray();

        if (\count($notAllowedValues) === 0) {
            return 'Not allowed values are not defined';
        }

        if (\in_array($cellValue, $notAllowedValues, true)) {
            return "Value \"<c>{$cellValue}</c>\" is not allowed";
        }

        return null;
    }
}
