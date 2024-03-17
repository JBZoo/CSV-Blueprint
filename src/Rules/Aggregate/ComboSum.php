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

use JBZoo\CsvBlueprint\Rules\AbstractCombo;

final class ComboSum extends AbstractCombo
{
    protected const NAME = 'sum';

    protected const HELP_TOP = [
        'Assumes that all values in the column are int/float only.',
        'An empty string is converted to null.',
    ];

    protected function getExpected(): float|int|string
    {
        return $this->getOptionAsFloat();
    }

    protected function getCurrent(array $columnValues): float|int|string
    {
        return \array_sum($cellValue);
    }
}
