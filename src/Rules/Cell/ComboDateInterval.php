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

final class ComboDateInterval extends AbstractCellRuleCombo
{
    protected const NAME = 'date interval';

    protected const INVALID_DATEINTERVAL_ACTUAL = -1;
    protected const INVALID_DATEINTERVAL_EXPECTED = -2;

    public function getHelpMeta(): array
    {
        return [
            [
                'Date Intervals. Under the hood, the strings are converted to seconds and compared.',
                'See: https://www.php.net/manual/en/class.dateinterval.php',
                'See: https://www.php.net/manual/en/dateinterval.createfromdatestring.php',
            ],
            [
                self::MIN     => ['PT0S', '0 seconds'],
                self::GREATER => ['1day 1sec', '1 day and 1 second'],
                self::EQ      => ['P2W', 'Exactly 2 weeks'],
                self::NOT     => ['100 days', 'Except for the 100 days'],
                self::LESS    => ['PT23H59M59S', '23 hours, 59 minutes, and 59 seconds'],
                self::MAX     => ['P1Y', '1 year'],
            ],
        ];
    }

    protected function getActualCell(string $cellValue): float
    {
        try {
            $seconds = self::dateIntervalToSeconds($cellValue);
        } catch (\Exception) {
            return self::INVALID_DATEINTERVAL_ACTUAL;
        }

        return $seconds;
    }

    protected function getExpected(): float
    {
        $expectedValue = $this->getOptionAsString();

        try {
            $seconds = self::dateIntervalToSeconds($expectedValue);
        } catch (\Exception) {
            return self::INVALID_DATEINTERVAL_EXPECTED;
        }

        return $seconds;
    }

    protected function getExpectedStr(): string
    {
        $expectedValue = $this->getOptionAsString();

        try {
            $seconds = self::dateIntervalToSeconds($expectedValue);
        } catch (\Exception $exception) {
            return "<red>{$exception->getMessage()}</red>";
        }

        return "{$seconds} ({$expectedValue}) seconds";
    }

    protected function getCurrentStr(string $cellValue): string
    {
        try {
            $seconds = self::dateIntervalToSeconds($cellValue);
        } catch (\Exception $exception) {
            return "<red>{$exception->getMessage()}</red>";
        }

        return "parsed as \"{$seconds}\" seconds";
    }

    private static function dateIntervalToSeconds(string $dateIntervalOrAsString): int
    {
        try {
            $interval = new \DateInterval($dateIntervalOrAsString);
        } catch (\Exception) {
            try {
                $interval = \DateInterval::createFromDateString($dateIntervalOrAsString);
            } catch (\Exception) {
                throw new \RuntimeException("Can't parse date interval: {$dateIntervalOrAsString}");
            }
        }

        if (!$interval instanceof \DateInterval) {
            throw new \RuntimeException("Can't parse date interval: {$dateIntervalOrAsString}");
        }

        $daysPerYear = 365.25;  // Average considering leap years
        $daysPerMonth = 30;     // Average. "365.25 / 12 ~ 30.4166666667"
        $hoursPerDay = 24;
        $minutesPerHour = 60;
        $secondsPerMinute = 60;

        $yearsToDays = $interval->y * $daysPerYear;
        $monthsToDays = $interval->m * $daysPerMonth;
        $days = $interval->d + $yearsToDays + $monthsToDays;
        $hours = $interval->h + ($days * $hoursPerDay);
        $minutes = $interval->i + ($hours * $minutesPerHour);
        $seconds = $interval->s + ($minutes * $secondsPerMinute);

        return (int)$seconds;
    }
}
