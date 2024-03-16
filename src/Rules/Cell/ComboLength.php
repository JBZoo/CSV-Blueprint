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

final class ComboLength extends AbstractCombo
{
    protected const NAME = 'length';

    protected const HELP_TOP = ['Checks length of a string including spaces (multibyte safe).'];

    /**
     * @phan-suppress PhanUnusedProtectedMethodParameter
     */
    protected function getExpected(): float|int|string
    {
        return $this->getOptionAsInt();
    }

    protected function getCurrent(string $cellValue): float|int|string
    {
        return \mb_strlen($cellValue);
    }
}
