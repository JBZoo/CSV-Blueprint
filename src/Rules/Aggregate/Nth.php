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

final class Nth extends AbstractAggregateRule
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_STRINGS;

    private const ARGS = 2;
    private const NTH = 0;
    private const VAL = 1;

    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['[ 2, Expected ]', 'Nth value in the column. Will be compared as strings.']],
        ];
    }

    public function validateRule(array $columnValues): ?string
    {
        if (\count($columnValues) === 0) {
            return null;
        }

        $params = $this->getOptionAsArray();
        if (\count($params) !== self::ARGS) {
            return 'The rule expects exactly two arguments: '
                . 'the first is the line number (without header), the second is the expected value';
        }

        $realLine = (int)$params[self::NTH];
        $arrayInd = $realLine - 1;
        $expValue = (string)$params[self::VAL];

        $actual = $columnValues[$arrayInd] ?? null;
        if ($actual === null) {
            return "The column does not have a line {$realLine}, so the value cannot be checked.";
        }

        if ($actual !== $expValue) {
            return "The value on line {$realLine} in the column is \"<c>{$actual}</c>\", "
                . "which is not equal than the expected \"<green>{$expValue}</green>\"";
        }

        return null;
    }
}
