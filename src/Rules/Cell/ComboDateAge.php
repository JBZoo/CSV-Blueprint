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

final class ComboDateAge extends AbstractCellRuleCombo
{
    protected const NAME = 'age';

    protected const INVALID_DATEINTERVAL_ACTUAL = -1;
    protected const INVALID_DATEINTERVAL_EXPECTED = -2;

    public function getHelpMeta(): array
    {
        return [
            [
                'Check an arbitrary date in a CSV cell for age (years).',
                'Actually it calculates the difference between the date and the current date.',
                'Convenient to use for age restrictions based on birthday.',
                'See the description of `date_*` functions for details on date formats.',
            ],
            [
                self::MIN     => [1, 'x >= 1'],
                self::GREATER => [14, 'x >  14'],
                self::NOT     => [18, 'x != 18'],
                self::EQ      => [21, 'x == 21'],
                self::LESS    => [99, 'x <  99'],
                self::MAX     => [100, 'x <= 100'],
            ],
        ];
    }

    protected function getActualCell(string $cellValue): float
    {
        try {
            $years = self::calculateAge($cellValue);
        } catch (\Exception) {
            return self::INVALID_DATEINTERVAL_ACTUAL;
        }

        return $years;
    }

    protected function getExpected(): float
    {
        return $this->getOptionAsInt();
    }

    protected function getExpectedStr(): string
    {
        return "{$this->getOptionAsInt()} years";
    }

    protected function getCurrentStr(string $cellValue): string
    {
        try {
            $years = self::calculateAge($cellValue);
        } catch (\Exception $exception) {
            return "<red>{$exception->getMessage()}</red>";
        }

        return "parsed as \"{$years}\" years";
    }

    private static function calculateAge(string $dateString): int
    {
        $birthDateTime = new \DateTimeImmutable($dateString);
        $currentDateTime = new \DateTimeImmutable('now');

        return $birthDateTime->diff($currentDateTime)->y; // Returns the total number of full years
    }
}
