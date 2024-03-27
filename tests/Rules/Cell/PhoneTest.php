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

use JBZoo\CsvBlueprint\Rules\Cell\Phone;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class PhoneTest extends TestAbstractCellRule
{
    protected string $ruleClass = Phone::class;

    public function testPositive(): void
    {
        $rule = $this->create('ALL');
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            '+1 650 253 00 00',
            '+7 (999) 999-99-99',
            '+7(999)999-99-99',
            '+7(999)999-9999',
            '+33(1)22 22 22 22',
            '+1 650 253 00 00',
            '+7 (999) 999-99-99',
            '+7(999)999-99-99',
            '+7(999)999-9999',
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), $value);
        }
    }

    public function testNegative(): void
    {
        $rule = $this->create('ALL');

        $invalid = [
            'qwerty',
            'qa234qwerty',
        ];

        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" has invalid phone number format.",
                $rule->test($value),
                $value,
            );
        }
    }

    public function testByCountryCode(): void
    {
        $rule = $this->create('BR');
        $valid = [
            '+55 11 91111 1111',
            '11 91111 1111',
            '+5511911111111',
            '11911111111',
            '11 91111 1111',
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), $value);
        }

        isSame(
            'The value "+7(999)999-9999" has invalid phone number format for country "BR".',
            $rule->test('+7(999)999-9999'),
        );
    }

    public function testInvalidCountryCode(): void
    {
        $rule = $this->create('QWERTY');
        isSame(
            '"phone" at line <red>1</red>, column "prop". Unexpected error: Invalid country code QWERTY.',
            (string)$rule->validate('+1 650 253 00 00'),
        );

        $rule = $this->create('');
        isSame(
            '"phone" at line <red>1</red>, column "prop". The country code is required. Example: "ALL", "US", "BR".',
            (string)$rule->validate('+1 650 253 00 00'),
        );
    }
}
