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

use JBZoo\CsvBlueprint\Rules\Cell\IsHex;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsHexTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsHex::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0x0'));
        isSame('', $rule->test('0x1'));
        isSame('', $rule->test('0x11'));
        isSame('', $rule->test('0x1F'));
        isSame('', $rule->test('0xff'));
        isSame('', $rule->test('0xfa'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "qwerty" is not a valid hexadecimal number. Example: "0x1A"',
            $rule->test('qwerty'),
        );

        $rule = $this->create(true);
        isSame(
            '"is_hex" at line <red>1</red>, column "prop". '
            . 'Value "<c>qwerty</c>" is not a valid hexadecimal number. Example: "0x1A".',
            (string)$rule->validate('qwerty'),
        );
    }
}
