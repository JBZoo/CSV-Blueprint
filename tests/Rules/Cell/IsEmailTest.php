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

use JBZoo\CsvBlueprint\Rules\Cell\IsEmail;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsEmailTest extends AbstractCellRule
{
    protected string $ruleClass = IsEmail::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('user@example.com'));
        isSame('', $rule->test('user@sub.example.com'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('user:pass@example.com'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "qwerty" is not a valid email',
            $rule->test('qwerty'),
        );
        isSame(
            'Value "user:pass@example.com" is not a valid email',
            $rule->test('user:pass@example.com'),
        );
    }
}
