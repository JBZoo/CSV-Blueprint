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
use MathPHP\Statistics\Descriptive;

use function JBZoo\Utils\float;

final class ComboPercentile extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'percentile';

    private const ARGS = 2;
    private const PERC = 0;
    private const VAL = 1;

    public function getHelpMeta(): array
    {
        return [
            [
                'Compute the P-th percentile of a list of numbers.',
                'Linear interpolation between closest ranks method - Second variant, '
                . 'C = 1 P-th percentile (0 <= P <= 100) of a list of N ordered values '
                . '(sorted from least to greatest).',
                'Similar method used in NumPy and Excel.',
                'See: https://en.wikipedia.org/wiki/Percentile#'
                . 'Second_variant.2C_.7F.27.22.60UNIQ--postMath-00000043-QINU.60.22.27.7F',
                'Example: `[ 95.5, 1.234 ]` The 95.5th percentile in the column must be "1.234" (float).',
            ],
            [
                self::MIN     => ['[ 95.0, 1.0 ]', 'x >= 1.0'],
                self::GREATER => ['[ 95.0, 2.0 ]', 'x >  2.0'],
                self::NOT     => ['[ 95.0, 5.0 ]', 'x != 5.0'],
                self::EQ      => ['[ 95.0, 7.0 ]', 'x == 7.0'],
                self::LESS    => ['[ 95.0, 8.0 ]', 'x <  8.0'],
                self::MAX     => ['[ 95.0, 9.0 ]', 'x <= 9.0'],
            ],
        ];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $result = self::calcValue($columnValues, ['p' => 95.0]);
        if ($result === null) {
            return false;
        }

        return [95.0, $result];
    }

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

        return self::calcValue($colValues, ['p' => $percentile]);
    }

    protected static function calcValue(array $columnValues, ?array $options = null): null|float|int
    {
        $columnValues = Utils::analyzeGuard($columnValues, self::INPUT_TYPE);
        if ($columnValues === null) {
            return null;
        }

        if (!isset($options['p'])) {
            throw new Exception('The rule expects the "p" option');
        }

        return Descriptive::percentile($columnValues, $options['p']);
    }

    private function getParams(): array
    {
        $params = $this->getOptionAsArray();
        if (\count($params) !== self::ARGS) {
            throw new Exception(
                'The rule expects exactly two params: '
                . 'the first is percentile (P is beet 0.0 and 100.0), the second is the expected value (float)',
            );
        }

        return $params;
    }
}
