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

final class ComboCountEven extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_INTS;

    protected const NAME = 'number of even values';

    public function getHelpMeta(): array
    {
        return [['Number of even values.'], []];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        return (int)\count(\array_filter($columnValues, static fn ($value) => (int)$value % 2 === 0));
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return \count(\array_filter($colValues, static fn ($value) => $value % 2 === 0));
    }
}
