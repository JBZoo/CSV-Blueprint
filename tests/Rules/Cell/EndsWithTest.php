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

use JBZoo\CsvBlueprint\Rules\Cell\EndsWith;
use JBZoo\PHPUnit\Rules\AbstractCellRuleTest;

use function JBZoo\PHPUnit\isSame;

final class EndsWithTest extends AbstractCellRuleTest
{
    protected string $ruleClass = EndsWith::class;

    public function testPositive(): void
    {
        $rule = $this->create('a');
        isSame('', $rule->test(''));
        isSame('', $rule->test('a'));
        isSame('', $rule->test('cba'));
        isSame('', $rule->test(''));
    }

    public function testNegative(): void
    {
        $rule = $this->create('a');
        isSame(
            'Value "a " must end with "a"',
            $rule->test('a '),
        );

        $rule = $this->create('');
        isSame(
            'Rule must contain a suffix value in schema file.',
            $rule->test('a '),
        );
    }
}
