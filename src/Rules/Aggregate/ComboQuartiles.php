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

final class ComboQuartiles extends AbstractAggregateRuleCombo
{
    public const INPUT_TYPE = AbstractRule::INPUT_TYPE_FLOATS;

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
                'Quartiles. Three points that divide the data set into four equal groups, '
                . 'each group comprising a quarter of the data.',
                'See: https://en.wikipedia.org/wiki/Quartile',
                // Options
                'There are multiple methods for computing quartiles: ' . Utils::printList(self::METHODS) . '. '
                . 'Exclusive is ussually classic.',
                'Available types: ' . Utils::printList(self::TYPES) . ' ("IQR" is Interquartile Range)',
                // Example
                'Example: `[ ' . self::METHODS[1] . ", '" . self::TYPES[3] . "', 42.0 ]`"
                . ' - the ' . self::TYPES[3] . ' ' . self::METHODS[1] . ' quartile is 42.0',
            ],
            [
                self::MIN     => ['[ ' . self::METHODS[0] . ", '" . self::TYPES[0] . "', 1.0 ]", 'x >= 1.0'],
                self::GREATER => ['[ ' . self::METHODS[1] . ", '" . self::TYPES[1] . "', 2.0 ]", 'x >  2.0'],
                self::NOT     => ['[ ' . self::METHODS[0] . ", '" . self::TYPES[2] . "', 5.0 ]", 'x != 5.0'],
                self::EQ      => ['[ ' . self::METHODS[1] . ", '" . self::TYPES[3] . "', 7.0 ]", 'x == 7.0'],
                self::LESS    => ['[ ' . self::METHODS[0] . ", '" . self::TYPES[4] . "', 8.0 ]", 'x <  8.0'],
                self::MAX     => ['[ ' . self::METHODS[1] . ", '" . self::TYPES[5] . "', 9.0 ]", 'x <= 9.0'],
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

        return self::calcValue($colValues, ['method' => $method, 'type' => $type]);
    }

    protected static function calcValue(array $columnValues, ?array $options = null): null|float|int
    {
        $columnValues = Utils::analyzeGuard($columnValues, self::INPUT_TYPE);
        if ($columnValues === null) {
            return null;
        }

        if (!isset($options['method'])) {
            throw new Exception('The rule expects the "method" option');
        }

        if (!isset($options['type'])) {
            throw new Exception('The rule expects the "type" option');
        }

        $result = Descriptive::quartiles($columnValues, $options['method']);

        if (!isset($result[$options['type']])) {
            throw new Exception("Unknown quartile type: {$options['type']}");
        }

        return $result[$options['type']];
    }

    private function getType(): string
    {
        $type = $this->getParams()[self::TYPE];

        if (!\in_array($type, self::TYPES, true)) {
            throw new Exception(
                "Unknown quartile type: \"{$type}\". Allowed: " . Utils::printList(self::TYPES, 'green'),
            );
        }

        return $type;
    }

    private function getMethod(): string
    {
        $method = $this->getParams()[self::METHOD];

        if (!\in_array($method, self::METHODS, true)) {
            throw new Exception(
                "Unknown quartile method: \"{$method}\". Allowed: " . Utils::printList(self::METHODS, 'green'),
            );
        }

        return $method;
    }

    private function getParams(): array
    {
        $params = $this->getOptionAsArray();
        if (\count($params) !== self::ARGS) {
            throw new Exception(
                'The rule expects exactly three params: '
                . 'method ' . Utils::printList(self::METHODS) . ', '
                . 'type ' . Utils::printList(self::TYPES) . ', '
                . 'expected value (float)',
            );
        }

        return $params;
    }
}
