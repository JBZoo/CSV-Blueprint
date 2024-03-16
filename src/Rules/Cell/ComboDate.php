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
    protected const HELP = [
        'Dates. Under the hood, the strings are converted to timestamp and compared.',
        'This gives you the ability to use relative dates and any formatting you want.',
        'By default, it works in UTC. But you can specify your own timezone as part of the date string.',
        'Format:    https://www.php.net/manual/en/datetime.format.php',
        'Parsing:   https://www.php.net/manual/en/function.strtotime.php',
        'Timezones: https://www.php.net/manual/en/timezones.php',
    ];

    private const OUTPUT_DATE_FORMAT = 'Y-m-d H:i:s P';

    public function getHelpCombo(
        array $equel = ['5'],
        array $not = ['42'],
        array $min = ['1'],
        array $max = ['10'],
    ): string {
        return parent::getHelpCombo(
            ['01 Jan 2000', 'You can use any string that can be parsed by the strtotime function'],
            ['2006-01-02 15:04:05 -0700 Europe/Rome'],
            ['+1 day', 'Examples of relative formats'],
            ['now', 'Examples of current date and time'],
        );
    }

    protected function getCurrent(string $cellValue): float|int|string
    {
        return (new \DateTimeImmutable($cellValue))->getTimestamp();
    }

    protected function getExpected(string $expectedValue): float|int|string
    {
        return (new \DateTimeImmutable($expectedValue))->getTimestamp();
    }

    protected function getExpectedStr(string $expectedValue): string
    {
        $formated = (new \DateTimeImmutable($expectedValue))->format(self::OUTPUT_DATE_FORMAT);

        return "{$formated} ({$expectedValue})";
    }

    protected function getCurrentStr(string $cellValue): string
    {
        $formated = (new \DateTimeImmutable($cellValue))->format(self::OUTPUT_DATE_FORMAT);

        return "parsed as \"{$formated}\"";
    }
}
