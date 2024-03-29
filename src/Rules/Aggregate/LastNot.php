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

use JBZoo\CsvBlueprint\Rules\AbstarctRule;

final class LastNot extends AbstractAggregateRule
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_STRINGS;

    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'Not expected',
                    'Not allowed as the last value in the column. Will be compared as strings.',
                ],
            ],
        ];
    }

    public function validateRule(array $columnValues): ?string
    {
        if (\count($columnValues) === 0) {
            return null;
        }

        $last = \end($columnValues);
        if ($last === $this->getOptionAsString()) {
            return "The last value in the column is \"<c>{$last}</c>\", " .
                "which is equal than the not allowed \"<green>{$this->getOptionAsString()}</green>\"";
        }

        return null;
    }
}
