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

use JBZoo\CsvBlueprint\Rules\Cell\IsOctal;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsOctalTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsOctal::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0o1'));
        isSame('', $rule->test('0o123'));

        isSame('', $rule->test('0O1'));
        isSame('', $rule->test('0O123'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "8" is not a valid octal number. Examples: "0o123"',
            $rule->test('8'),
        );

        $rule = $this->create(true);
        isSame(
            '"is_octal" at line <red>1</red>, column "prop". Value "<c>qwerty</c>" is not a valid octal number. '
            . 'Examples: "0o123".',
            (string)$rule->validate('qwerty'),
        );
    }
}
