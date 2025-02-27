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

final class ContainsNone extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['[ a, b ]', 'All the strings must NOT be part of a CSV value.']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $exclusions = $this->getOptionAsArray();
        if (\count($exclusions) === 0) {
            return 'Rule must contain at least one exclusion value in schema file.';
        }

        foreach ($exclusions as $exclusion) {
            if (\strpos($cellValue, $exclusion) !== false) {
                return "Value \"<c>{$cellValue}</c>\" must not contain the string: "
                    . Utils::printList($exclusion, 'green');
            }
        }

        return null;
    }
}
