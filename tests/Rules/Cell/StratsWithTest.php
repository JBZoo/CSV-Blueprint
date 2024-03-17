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

use JBZoo\CsvBlueprint\Rules\Cell\StartsWith;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class StratsWithTest extends AbstractCellRule
{
    protected string $ruleClass = StartsWith::class;

    public function testPositive(): void
    {
        $rule = $this->create('a');
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('a'));
        isSame('', $rule->test('abc'));
        isSame(null, $rule->validate(''));
    }

    public function testNegative(): void
    {
        $rule = $this->create('a');

        isSame(
            'Value " a" must start with "a"',
            $rule->test(' a'),
        );

        $rule = $this->create('');
        isSame(
            'Rule must contain a prefix value in schema file.',
            $rule->test('a '),
        );
    }
}
