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

use JBZoo\CsvBlueprint\Utils;

class IsTimezoneOffset extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['true', 'Allow only timezone offsets. Example: "+03:00".']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        if (Utils::testRegex('/^[\+\-](0\d|1[0-4]):([0-5]\d)$/', $cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid timezone offset. " .
                'Example: "<green>+03:00</green>".';
        }

        return null;
    }
}
