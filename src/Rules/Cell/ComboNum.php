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

use JBZoo\CsvBlueprint\Rules\AbstractCombo;

class ComboNum extends AbstractCombo
{
    protected string $name = 'number';
    protected string $help = 'Validation of the value as an integer/decimal number.';

    /**
     * @phan-suppress PhanUnusedProtectedMethodParameter
     */
    protected function getExpected(string $cellValue): float|int|string
    {
        return $this->getOptionAsFloat();
    }

    protected function getCurrent(string $cellValue): float|int|string
    {
        return (float)$cellValue;
    }
}
