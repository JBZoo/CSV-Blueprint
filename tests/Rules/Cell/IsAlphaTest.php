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

use JBZoo\CsvBlueprint\Rules\Cell\IsAlpha;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsAlphaTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsAlpha::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            'b',
            'c',
            'D',
            'df',
            'Bcdf',
            'fdbC',
            'aBc',
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
            '!@#$%^&*()',
            'aBc123',
            'aBc 123',
            'aBc-123',
        ];

        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" should contain only alphabetic characters. Example: \"aBc\"",
                $rule->test($value),
                $value,
            );
        }
    }
}
