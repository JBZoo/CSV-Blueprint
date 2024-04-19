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

use JBZoo\CsvBlueprint\Rules\Cell\IsUsaState;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsUsaStateTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsUsaState::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->test(''));
        isSame('', $rule->test('NY'));
        isSame('', $rule->test('New York'));

        isSame('', $rule->test('ny'));
        isSame('', $rule->test('new york'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "qwe" is not a valid USA state name or code',
            $rule->test('qwe'),
        );
    }
}
