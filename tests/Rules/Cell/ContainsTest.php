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

use JBZoo\CsvBlueprint\Rules\Cell\Contains;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class ContainsTest extends TestAbstractCellRule
{
    protected string $ruleClass = Contains::class;

    public function testPositive(): void
    {
        $rule = $this->create('');
        isSame('', $rule->test(''));

        $rule = $this->create('a');
        isSame('', $rule->test(''));
        isSame('', $rule->test('a'));
        isSame('', $rule->test('abc'));
        isSame('', $rule->test('adasdasdasdc'));
    }

    public function testNegative(): void
    {
        $rule = $this->create('');
        isSame(
            'Rule must contain at least one char in schema file.',
            $rule->test('123'),
        );

        $rule = $this->create('a');
        isSame(
            'Value "123" must contain "a"',
            $rule->test('123'),
        );
    }
}
