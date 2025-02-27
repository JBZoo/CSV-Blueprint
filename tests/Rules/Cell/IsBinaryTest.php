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

use JBZoo\CsvBlueprint\Rules\Cell\IsBinary;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsBinaryTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsBinary::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0'));
        isSame('', $rule->test('1'));
        isSame('', $rule->test('11'));
        isSame('', $rule->test('10101010'));

        isSame('', $rule->test('0b1'));
        isSame('', $rule->test('0B0'));
        isSame('', $rule->test('0b110101'));
        isSame('', $rule->test('0B110101'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "qwerty" is not a valid binary number. Example: "0b10" or "10"',
            $rule->test('qwerty'),
        );

        $rule = $this->create(true);
        isSame(
            '"is_binary" at line <red>1</red>, column "prop". '
            . 'Value "<c>qwerty</c>" is not a valid binary number. Example: "0b10" or "10".',
            (string)$rule->validate('qwerty'),
        );
    }
}
