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

use JBZoo\CsvBlueprint\Rules\Cell\IsIban;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsIbanTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsIban::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            'BE71 0961 2345 6769',                  // 'Belgium'
            'FR76 3000 6000 0112 3456 7890 189',    // 'France'
            'DE89 3704 0044 0532 0130 00',          // 'Germany'
            'GR96 0810 0010 0000 0123 4567 890',    // 'Greece'
            'RO09 BCYP 0000 0012 3456 7890',        // 'Romania'
            'SA44 2000 0001 2345 6789 1234',        // 'Saudi Arabia'
            'ES79 2100 0813 6101 2345 6789',        // 'Spain'
            'SE35 5000 0000 0549 1000 0003',        // 'Sweden'
            'CH56 0483 5012 3456 7800 9',           // 'Switzerland'
            'CH9300762011623852957',                // 'Switzerland without spaces'
            'GB98 MIDL 0700 9312 3456 78',          // 'United Kingdom'
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), $value);
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = [
            ';',
            ' ',
            'ZZ32 5000 5880 7742',
            '123456789',
            'aBc 123',
            'aBc-123',
        ];

        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" is not a valid IBAN number.",
                $rule->test($value),
                $value,
            );
        }
    }
}
