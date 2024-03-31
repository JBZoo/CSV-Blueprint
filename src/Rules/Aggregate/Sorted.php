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
use JBZoo\CsvBlueprint\Utils;

final class Sorted extends AbstractAggregateRule
{
    public const INPUT_TYPE = AbstarctRule::INPUT_TYPE_STRINGS;

    private const ARGS = 2;
    private const DIR = 0;
    private const METHOD = 1;

    private const DIRS = ['asc', 'desc'];
    private const METHODS = [
        'natural' => \SORT_NATURAL,
        'regular' => \SORT_REGULAR,
        'numeric' => \SORT_NUMERIC,
        'string'  => \SORT_STRING,
    ];

    public function getHelpMeta(): array
    {
        return [
            [
                'Check if the column is sorted in a specific order.',
                ' - Direction: ' . Utils::printList(self::DIRS) . '.',
                ' - Method:    ' . Utils::printList(\array_keys(self::METHODS)) . '.',
                'See: https://www.php.net/manual/en/function.sort.php',
            ],
            [
                self::DEFAULT => ['[ asc, natural ]', 'Expected ascending order, natural sorting.'],
            ],
        ];
    }

    public function validateRule(array $columnValues): ?string
    {
        if (\count($columnValues) === 0) {
            return null;
        }

        try {
            $dir = $this->getDir();
            $method = $this->getMethod();
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }

        $methodHuman = $this->getParams()[self::METHOD];

        $sorted = $columnValues; // copy
        if ($dir === self::DIRS[0]) {
            \sort($sorted, $method);
        } else {
            \rsort($sorted, $method);
        }

        if ($sorted !== $columnValues) {
            return "The column is not sorted \"<green>{$dir}</green>\" using method \"<green>{$methodHuman}</green>\"";
        }

        return null;
    }

    private function getDir(): string
    {
        $dir = $this->getParams()[self::DIR];

        if (!\in_array($dir, self::DIRS, true)) {
            throw new \RuntimeException(
                "Unknown sort direction: \"{$dir}\". Allowed: " . Utils::printList(self::DIRS, 'green'),
            );
        }

        return $dir;
    }

    private function getMethod(): int
    {
        $method = $this->getParams()[self::METHOD];

        if (!\in_array($method, \array_keys(self::METHODS), true)) {
            throw new \RuntimeException(
                "Unknown sort method: \"{$method}\". Allowed: " . Utils::printList(\array_keys(self::METHODS), 'green'),
            );
        }

        return self::METHODS[$method];
    }

    private function getParams(): array
    {
        $params = $this->getOptionAsArray();
        if (\count($params) !== self::ARGS) {
            throw new \RuntimeException(
                'The rule expects exactly two params: ' .
                'direction ' . Utils::printList(self::DIRS) . ' and ' .
                'method ' . Utils::printList(\array_keys(self::METHODS)),
            );
        }

        return $params;
    }
}
