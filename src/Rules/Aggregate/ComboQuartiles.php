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

final class ComboQuartiles extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_FLOATS;

    protected const NAME = 'quartile';

    private const TYPES = ['0%', 'Q1', 'Q2', 'Q3', '100%', 'IQR'];
    private const METHODS = ['exclusive', 'inclusive'];

    private const ARGS = 3;
    private const METHOD = 0;
    private const TYPE = 1;
    private const VAL = 2;

    public function getHelpMeta(): array
    {
        return [
            [
                'Quartiles. Three points that divide the data set into four equal groups, ' .
                'each group comprising a quarter of the data.',
                'See: https://en.wikipedia.org/wiki/Quartile',
                // Options
                'There are multiple methods for computing quartiles: "' . \implode('", "', self::METHODS) . '". ' .
                'Exclusive is ussually classic.',
                'Available types: "' . \implode('", "', self::TYPES) . '" (aka Interquartile Range)',
                // Example
                'Example: `[ ' . self::METHODS[1] . ", '" . self::TYPES[3] . "', 42.0 ]`" .
                ' - the ' . self::TYPES[3] . ' ' . self::METHODS[1] . ' quartile is 50.0',
            ],
            [
                self::MIN     => ["[ '" . self::METHODS[0] . "', '" . self::TYPES[0] . "', 1.0 ]", 'x >= 1.0'],
                self::GREATER => ["[ '" . self::METHODS[1] . "', '" . self::TYPES[1] . "', 2.0 ]", 'x >  2.0'],
                self::NOT     => ["[ '" . self::METHODS[0] . "', '" . self::TYPES[2] . "', 5.0 ]", 'x != 5.0'],
                self::EQ      => ["[ '" . self::METHODS[1] . "', '" . self::TYPES[3] . "', 7.0 ]", 'x == 7.0'],
                self::LESS    => ["[ '" . self::METHODS[0] . "', '" . self::TYPES[4] . "', 8.0 ]", 'x <  8.0'],
                self::MAX     => ["[ '" . self::METHODS[1] . "', '" . self::TYPES[5] . "', 9.0 ]", 'x <= 9.0'],
            ],
        ];
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

        $method = $this->getMethod();
        $type = $this->getType();
        $result = Descriptive::quartiles(self::stringsToFloat($colValues), $method);

        return $result[$type];
    }

    private function getType(): string
    {
        $allowedTypes = ['0%', 'Q1', 'Q2', 'Q3', '100%', 'IQR'];

        $type = $this->getParams()[self::TYPE];

        if (!\in_array($type, $allowedTypes, true)) {
            throw new \RuntimeException(
                "Unknown quartile type: \"{$type}\". Allowed: \"" . \implode('", "', $allowedTypes) . '"',
            );
        }

        return $type;
    }

    private function getMethod(): string
    {
        $allowedMethods = ['exclusive', 'inclusive'];

        $method = $this->getParams()[self::METHOD];

        if (!\in_array($method, $allowedMethods, true)) {
            throw new \RuntimeException(
                "Unknown quartile method: \"{$method}\". Allowed: \"" . \implode('", "', $allowedMethods) . '"',
            );
        }

        return $method;
    }

    private function getParams(): array
    {
        $params = $this->getOptionAsArray();
        if (\count($params) !== self::ARGS) {
            throw new \RuntimeException(
                'The rule expects exactly three params: ' .
                'method (exclusive, inclusive), type (0%, Q1, Q2, Q3, 100%, IQR), expected value (float)',
            );
        }

        return $params;
    }
}
