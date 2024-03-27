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

use JBZoo\CsvBlueprint\Rules\Cell\IsPunct;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsPunctTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsPunct::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            '.',
            ',;:',
            '-@#$*',
            '()[]{}',
            '!@#$%^&*(){}',
            "[]?+=/\\-_|\"',><.",
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
            '16-50',
            'a',
            ' ',
            'Foo',
            '12.1',
            '-12',
            '( )_{}',
        ];

        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" should be composed by only punctuation characters.",
                $rule->test($value),
                $value,
            );
        }
    }
}
