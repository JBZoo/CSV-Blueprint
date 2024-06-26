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
use JBZoo\CsvBlueprint\Utils;

final class ComboCountEven extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_INTS;

    protected const NAME = 'number of even values';

    public function getHelpMeta(): array
    {
        return [['Number of even values.'], []];
    }

    protected static function calcValue(array $columnValues, ?array $options = null): null|float|int
    {
        $columnValues = Utils::analyzeGuard($columnValues, self::INPUT_TYPE);
        if ($columnValues === null) {
            return null;
        }

        return \count(\array_filter($columnValues, static fn ($value) => $value % 2 === 0));
    }
}
