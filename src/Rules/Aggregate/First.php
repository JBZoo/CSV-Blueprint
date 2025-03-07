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

use JBZoo\CsvBlueprint\Rules\AbstractRule;

final class First extends AbstractAggregateRule
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_STRINGS;

    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['Expected', 'First value in the column. Will be compared as strings.']],
        ];
    }

    public function validateRule(array $columnValues): ?string
    {
        if (\count($columnValues) === 0) {
            return null;
        }

        $first = \reset($columnValues);
        if ($first !== $this->getOptionAsString()) {
            return "The first value in the column is \"<c>{$first}</c>\", "
                . "which is not equal than the expected \"<green>{$this->getOptionAsString()}</green>\"";
        }

        return null;
    }
}
