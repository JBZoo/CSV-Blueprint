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
use MathPHP\Statistics\Descriptive;

use function JBZoo\Utils\float;

final class ComboPercentile extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME     = 'percentile';
    protected const HELP_TOP = [
        'Compute the P-th percentile of a list of numbers.',
        'Linear interpolation between closest ranks method - Second variant, ',
        'C = 1 P-th percentile (0 <= P <= 100) of a list of N ordered values (sorted from least to greatest).' .
        'Similar method used in NumPy and Excel',
        'See: https://en.wikipedia.org/wiki/Percentile#' .
        'Second_variant.2C_.7F.27.22.60UNIQ--postMath-00000043-QINU.60.22.27.7F',
    ];

    protected const HELP_OPTIONS = [
        self::EQ => [
            '[ 95, 1.234 ]',
            'Example: The 95th percentile in the column must be "1.234" (float)',
        ],
        self::NOT => ['[ 95, 4.123 ]', ''],
        self::MIN => ['[ 95, -1 ]', ''],
        self::MAX => ['[ 95, 2e4 ]', ''],
    ];

    private const ARGS = 2;
    private const PERC = 0;
    private const VAL  = 1;

    protected function getExpected(): float
    {
        return float($this->getParams()[self::VAL]);
    }

    protected function getActualAggregate(array $colValues): ?float
    {
        if (\count($colValues) === 0) {
            return null;
        }

        $percentile = (float)$this->getParams()[self::PERC];

        if ($percentile < 0 || $percentile > 100) {
            throw new \RuntimeException(
                "The percentile value must be between 0 and 100, but \"<c>{$percentile}</c>\" given",
            );
        }

        return Descriptive::percentile(self::stringsToFloat($colValues), $percentile);
    }

    private function getParams(): array
    {
        $params = $this->getOptionAsArray();
        if (\count($params) !== self::ARGS) {
            throw new \RuntimeException(
                'The rule expects exactly two arguments: ' .
                'the first is the line number (without header), the second is the expected value',
            );
        }

        return $params;
    }
}
