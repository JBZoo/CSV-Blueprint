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

use JBZoo\CsvBlueprint\Rules\Cell\IsDirExists;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsDirExistsTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsDirExists::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test(__DIR__));
        isSame('', $rule->test(__DIR__ . '/'));
        isSame('', $rule->test(__DIR__ . '/../'));
        isSame('', $rule->test(__DIR__ . '/../../'));
        isSame('', $rule->test(PROJECT_ROOT));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Directory "qwerty" not found',
            $rule->test('qwerty'),
        );
    }
}
