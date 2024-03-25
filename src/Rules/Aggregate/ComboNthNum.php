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

use function JBZoo\Utils\float;

final class ComboNthNum extends AbstarctAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME     = 'N-th value';
    protected const HELP_TOP = [
        'N-th value in the column.',
        'The rule expects exactly two arguments: ' .
        'the first is the line number (without header), the second is the expected value.',
        'Example: `[ 42, 5.0 ]` On the line 42 (disregarding the header), we expect the 5.0. The comparison is always as float.',
    ];

    protected const HELP_OPTIONS = [
        self::MIN     => ['[ 42, 1.0 ]', 'x >= 1.0'],
        self::GREATER => ['[ 42, 2.0 ]', 'x >  2.0'],
        self::NOT     => ['[ 42, 5.0 ]', 'x != 5.0'],
        self::EQ      => ['[ 42, 7.0 ]', 'x == 7.0'],
        self::LESS    => ['[ 42, 8.0 ]', 'x <  8.0'],
        self::MAX     => ['[ 42, 9.0 ]', 'x <= 9.0'],
    ];

    private const ARGS = 2;
    private const NTH  = 0;
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

        $realLine = (int)$this->getParams()[self::NTH];
        $arrayInd = $realLine - 1;
        $actual   = $colValues[$arrayInd] ?? null;

        if ($actual === null) {
            throw new \RuntimeException(
                "The column does not have a line {$realLine}, so the value cannot be checked.",
            );
        }

        return float($actual);
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
