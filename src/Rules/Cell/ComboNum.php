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

use function JBZoo\Utils\float;

final class ComboNum extends AbstractCombo
{
    protected const NAME     = 'number';
    protected const HELP_TOP = [
        'Under the hood it convertes and compares as float values.',
        'Comparison accuracy is ' . self::PRECISION . ' digits after a dot.',
        'Scientific number format is also supported. Example: "1.2e3"',
    ];

    private const PRECISION = 12;

    /**
     * @phan-suppress PhanUnusedProtectedMethodParameter
     */
    protected function getExpected(): float|int|string
    {
        return float($this->getOptionAsString(), self::PRECISION);
    }

    protected function getCurrentStr(string $cellValue): string
    {
        return '';
    }

    protected function getCurrent(string $cellValue): float|int|string
    {
        return float($cellValue, self::PRECISION);
    }
}
