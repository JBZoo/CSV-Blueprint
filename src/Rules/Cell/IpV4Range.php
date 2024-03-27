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
use Respect\Validation\Validator;

final class IpV4Range extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    "[ '127.0.0.1-127.0.0.5', '127.0.0.0/21' ]",
                    'Check subnet mask or range for IPv4. Address must be in one of the ranges.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        $ranges = $this->getOptionAsArray();

        if (\count($ranges) === 0) {
            return 'IPv4 range is not defined.';
        }

        foreach ($ranges as $range) {
            if (Validator::ip($range, \FILTER_FLAG_IPV4)->validate($cellValue)) {
                return null;
            }
        }

        return "Value \"<c>{$cellValue}</c>\" is not included in any of IPv4 the ranges: " .
            '[' . Utils::printList($ranges, 'green') . ']';
    }
}
