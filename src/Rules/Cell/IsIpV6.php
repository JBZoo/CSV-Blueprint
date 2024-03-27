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

use Respect\Validation\Validator;

final class IsIpV6 extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Only IPv6. Example: "2001:0db8:85a3:08d3:1319:8a2e:0370:7334".'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!Validator::ip('*', \FILTER_FLAG_IPV6)->validate($cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" is not a valid IPv6";
        }

        return null;
    }
}
