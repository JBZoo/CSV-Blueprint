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

final class ContainsOne extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['[ a, b ]', 'Only one of the strings must be part of the CSV value.']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $inclusions = $this->getOptionAsArray();
        if (\count($inclusions) === 0) {
            return 'Rule must contain at least one inclusion value in schema file.';
        }

        $found = 0;
        foreach ($inclusions as $inclusion) {
            if (\strpos($cellValue, $inclusion) !== false) {
                $found++;
            }
        }

        if ($found === 1) {
            return null;
        }

        return "Value \"<c>{$cellValue}</c>\" must contain exactly one of the following:" .
            ' [' . Utils::printList($inclusions, 'green') . ']';
    }
}
