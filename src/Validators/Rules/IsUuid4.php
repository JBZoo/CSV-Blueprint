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

final class IsUuid4 extends AbstarctRule
{
    public function validateRule(?string $cellValue): ?string
    {
        if (!$this->getOptionAsBool()) {
            return null;
        }

        $uuid4 = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89ABab][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/';

        if (\preg_match($uuid4, (string)$cellValue) === 0) {
            return 'Value is not a valid UUID v4';
        }

        return null;
    }
}
