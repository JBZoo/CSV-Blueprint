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
use MathPHP\Statistics\Average;

final class ComboContraharmonicMean extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'contraharmonic mean';

    public function getHelpMeta(): array
    {
        return [
            [
                'Contraharmonic mean. A function complementary to the harmonic mean. '
                . 'A special case of the Lehmer mean, L₂(x), where p = 2.',
                'See: https://en.wikipedia.org/wiki/Contraharmonic_mean',
            ],
            [],
        ];
    }

    protected static function calcValue(array $columnValues, ?array $options = null): null|float|int
    {
        $columnValues = Utils::analyzeGuard($columnValues, self::INPUT_TYPE);
        if ($columnValues === null) {
            return null;
        }

        return Average::contraharmonicMean($columnValues);
    }
}
