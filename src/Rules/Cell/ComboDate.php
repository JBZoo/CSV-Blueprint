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

use JBZoo\CsvBlueprint\Rules\AbstractCombo;

final class ComboDate extends AbstractCombo
{
    protected const NAME = 'date';

    protected const HELP_TOP = [
        'Dates. Under the hood, the strings are converted to timestamp and compared.',
        'This gives you the ability to use relative dates and any formatting you want.',
        'By default, it works in UTC. But you can specify your own timezone as part of the date string.',
        'Format:    https://www.php.net/manual/en/datetime.format.php',
        'Parsing:   https://www.php.net/manual/en/function.strtotime.php',
        'Timezones: https://www.php.net/manual/en/timezones.php',
    ];

    protected const HELP_OPTIONS = [
        self::EQ  => ['01 Jan 2000', 'You can use any string that can be parsed by the strtotime function'],
        self::NOT => ['2006-01-02 15:04:05 -0700 Europe/Rome'],
        self::MIN => ['+1 day', 'Examples of relative formats'],
        self::MAX => ['now', 'Examples of current date and time'],
    ];

    private const OUTPUT_DATE_FORMAT = 'Y-m-d H:i:s P';

    protected function getCurrent(string $cellValue): float|int|string
    {
        try {
            $result = (new \DateTimeImmutable($cellValue))->getTimestamp();
        } catch (\Exception) {
            return -1;
        }

        return $result;
    }

    protected function getExpected(): float|int|string
    {
        $expectedValue = $this->getOptionAsString();

        try {
            $result = (new \DateTimeImmutable($expectedValue))->getTimestamp();
        } catch (\Exception) {
            return -1;
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
}
