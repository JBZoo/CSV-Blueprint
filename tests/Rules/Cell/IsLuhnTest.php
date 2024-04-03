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

use JBZoo\CsvBlueprint\Rules\Cell\IsLuhn;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsLuhnTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsLuhn::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            '2222400041240011',
            '340316193809364',
            '6011000990139424',
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
            '2222400041240021',
            '222240004124001.1',
            '2222400041240021',
            'aBc 123',
            'aBc-123',
        ];

        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" is not a valid Luhn number.",
                $rule->test($value),
                $value,
            );
        }
    }
}
