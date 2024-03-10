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

final class IsLongitude extends IsFloat
{
    public function validateRule(?string $cellValue): ?string
    {
        if (!$this->getOptionAsBool()) {
            return null;
        }

        $result = parent::validateRule($cellValue);
        if ($result !== null) {
            return $result;
        }

        $latitude = (float)$cellValue;
        if ($latitude < -180.0 || $latitude > 180.0) {
            return "Value \"{$cellValue}\" is not a valid longitude (-180 <= x <= 180)";
        }

        return null;
    }
}
