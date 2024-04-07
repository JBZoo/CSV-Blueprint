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

use JBZoo\CsvBlueprint\Rules\Cell\IsPasswordSafeChars;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsPasswordSafeCharsTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsPasswordSafeChars::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('qwerty'));
        isSame('', $rule->test('1231231'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('The value "🔥" as password uses not safe characters.', $rule->test('🔥'));
    }
}
