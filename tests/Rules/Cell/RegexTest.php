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

use JBZoo\CsvBlueprint\Rules\Cell\Regex;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class RegexTest extends TestAbstractCellRule
{
    protected string $ruleClass = Regex::class;

    public function testPositive(): void
    {
        $rule = $this->create('/^a/');
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('abc'));
        isSame('', $rule->test('aaa'));
        isSame('', $rule->test('a'));
        isSame('', $rule->test(''));

        $rule = $this->create('^a');
        isSame('', $rule->test('abc'));
        isSame('', $rule->test('aaa'));
        isSame('', $rule->test('a'));
    }

    public function testNegative(): void
    {
        $rule = $this->create('/^a/');
        isSame('Value "1bc" does not match the pattern "/^a/"', $rule->test('1bc'));

        $rule = $this->create('^a');
        isSame('Value "1bc" does not match the pattern "/^a/"', $rule->test('1bc'));

        $rule = $this->create('');
        isSame('Regex pattern is not defined', $rule->test('1bc'));

        // $rule = $this->create('/()*#@UIE/W09');
        // isSame('Regex pattern has invalid syntax: /()*#@UIE/W09', $rule->test('1bc'));

        // $rule = $this->create('/^[a-zA-Z\s&/-]+$/');
        // isSame('Regex pattern has invalid syntax: /^[a-zA-Z\s&/-]+$/', $rule->test('1bc'));
    }
}
