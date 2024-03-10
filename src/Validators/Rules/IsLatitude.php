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

namespace JBZoo\CsvBlueprint\Validators\Rules;

final class IsLatitude extends IsFloat
{
    public function validateRule(?string $cellValue): ?string
    {
        if (!$this->getOptionAsBool()) {
            return null;
        }

        $result = parent::validateRule($cellValue);
        if ($result !== '') {
            return $result;
        }

        $latitude = (float)$cellValue;
        if ($latitude < -90.0 || $latitude > 90.0) {
            return "Value \"{$cellValue}\" is not a valid latitude (-90 <= x <= 90)";
        }

        return null;
    }
}
