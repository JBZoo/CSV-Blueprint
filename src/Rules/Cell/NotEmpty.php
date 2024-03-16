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

final class NotEmpty extends AbstarctCellRule
{
    protected const HELP_OPTIONS = [
        self::DEFAULT => ['true', 'Value is not an empty string. Actually checks if the string length is not 0.'],
    ];

    public function validateRule(string $cellValue): ?string
    {
        if (!$this->getOptionAsBool()) {
            return null;
        }

        if ($cellValue === '') {
            return 'Value is empty';
        }

        return null;
    }
}
