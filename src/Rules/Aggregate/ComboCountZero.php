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

use Ds\Vector;
use JBZoo\CsvBlueprint\Rules\AbstarctRule;

final class ComboCountZero extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_INTS;

    protected const NAME = 'number of zero values';

    public function getHelpMeta(): array
    {
        return [
            [
                'Number of zero values. ' .
                "Any text and spaces (i.e. anything that doesn't look like a number) will be converted to 0.",
            ],
            [],
        ];
    }

    protected function getActualAggregate(Vector $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return \count(\array_filter($colValues, static fn ($value) => (float)$value === 0.0));
    }
}
