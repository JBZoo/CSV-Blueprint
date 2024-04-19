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

namespace JBZoo\CsvBlueprint\Rules\Cell;

final class ComboDate extends AbstractCellRuleCombo
{
    protected const NAME = 'date';

    private const OUTPUT_DATE_FORMAT = 'Y-m-d H:i:s P';
    private const SUGGEST_DATE_FORMAT = 'Y-m-d';
    private const INVALID_TIMESTAMP = -1;

    public function getHelpMeta(): array
    {
        return [
            [
                'Dates. Under the hood, the strings are converted to timestamp and compared.',
                'This gives you the ability to use relative dates and any formatting you want.',
                'By default, it works in UTC. But you can specify your own timezone as part of the date string.',
                'Format:    https://www.php.net/manual/en/datetime.format.php',
                'Parsing:   https://www.php.net/manual/en/function.strtotime.php',
                'Timezones: https://www.php.net/manual/en/timezones.php',
            ],
            [
                self::MIN     => ['-100 years', 'Example of relative past date'],
                self::GREATER => ['-99 days', 'Example of relative formats'],
                self::EQ      => ['01 Jan 2000', 'You can use any string that can be parsed by the strtotime function'],
                self::NOT     => ['2006-01-02 15:04:05 -0700 Europe/Rome'],
                self::LESS    => ['now', 'Example of current date and time'],
                self::MAX     => ['+1 day', 'Example of relative future date'],
            ],
        ];
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $min = null;
        $max = null;

        foreach ($columnValues as $cellValue) {
            if (!IsDate::testValue($cellValue)) {
                return false;
            }

            $timestamp = self::convertToTimestamp($cellValue);
            if ($timestamp === self::INVALID_TIMESTAMP) {
                return false;
            }

            if ($min === null || $timestamp < $min) {
                $min = $timestamp;
            }

            if ($max === null || $timestamp > $max) {
                $max = $timestamp;
            }
        }

        return $max === $min
            ? ['' => \date(self::SUGGEST_DATE_FORMAT, (int)$max)]
            : [
                'min' => \date(self::SUGGEST_DATE_FORMAT, (int)$min),
                'max' => \date(self::SUGGEST_DATE_FORMAT, (int)$max),
            ];
    }

    protected function getActualCell(string $cellValue): float
    {
        return self::convertToTimestamp($cellValue);
    }

    protected function getExpected(): float
    {
        $expectedValue = $this->getOptionAsString();

        try {
            $result = (new \DateTimeImmutable($expectedValue))->getTimestamp();
        } catch (\Exception) {
            return self::INVALID_TIMESTAMP;
        }

        return $result;
    }

    protected function getExpectedStr(): string
    {
        $expectedValue = $this->getOptionAsString();

        try {
            $formated = (new \DateTimeImmutable($expectedValue))->format(self::OUTPUT_DATE_FORMAT);
        } catch (\Exception) {
            return "Can't parse date: {$expectedValue}";
        }

        return "{$formated} ({$expectedValue})";
    }

    protected function getCurrentStr(string $cellValue): string
    {
        try {
            $formated = (new \DateTimeImmutable($cellValue))->format(self::OUTPUT_DATE_FORMAT);
        } catch (\Exception) {
            $formated = "Can't parse date: {$cellValue}";
        }

        return "parsed as \"{$formated}\"";
    }

    private static function convertToTimestamp(string $date): int
    {
        try {
            $result = (new \DateTimeImmutable($date))->getTimestamp();
        } catch (\Exception) {
            return self::INVALID_TIMESTAMP;
        }

        return $result;
    }
}
