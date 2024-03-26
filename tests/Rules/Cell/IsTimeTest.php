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

namespace JBZoo\PHPUnit\Rules\Cell;

use JBZoo\CsvBlueprint\Rules\Cell\IsTime;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsTimeTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsTime::class;

    public function testPositive(): void
    {
        $flags = [' am', ' AM', ' pm', ' PM', ''];
        $hours24 = ['0', '00', '01', '23'];
        $hoursAm = ['1', '01', '12'];
        $minutes = ['00', '01', '59'];
        $seconds = ['00', '01', '59'];

        $rule = $this->create(true);
        isSame('', $rule->test(''));
        foreach ($flags as $flag) {
            foreach ($hoursAm as $hour) {
                foreach ($minutes as $minute) {
                    foreach ($seconds as $second) {
                        isSame('', $rule->test("{$hour}:{$minute}:{$second}{$flag}"));
                    }
                }
            }
        }

        foreach ($hours24 as $hour) {
            foreach ($minutes as $minute) {
                foreach ($seconds as $second) {
                    isSame('', $rule->test("{$hour}:{$minute}:{$second}"));
                }
            }
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalidTimeFormats = [
            '1',
            '1.00',
            '25:00',
            '23:60',
            '23:00 am',
            '+20:69',
            'utc',
            '12:34:56:78',
            '12:34:56:78 am',
            '25:00:00',
            '25:00:00 am',
            '23:60:00',
            '23:60:00 am',
            '23:00:60',
            '23:00:60 am',
            '23:00:60 pm',
            '23:00:60 pm',
            '12:34  am',
            '23:34 pm',
        ];

        foreach ($invalidTimeFormats as $invalidTimeFormat) {
            isSame(
                "Value \"{$invalidTimeFormat}\" is not a valid time. " .
                'Example: "12:34:56 AM", "23:34:56", "12:34"',
                $rule->test($invalidTimeFormat),
            );
        }
    }
}
