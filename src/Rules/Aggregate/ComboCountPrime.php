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
use Respect\Validation\Validator;

final class ComboCountPrime extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_INTS;

    protected const NAME = 'number of prime values';

    public function getHelpMeta(): array
    {
        return [['Number of prime values.'], []];
    }

    protected function getActualAggregate(Vector $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        return \count(
            \array_filter(
                $colValues,
                static fn ($value) => Validator::primeNumber()->validate($value),
            ),
        );
    }
}
