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

use JBZoo\CsvBlueprint\Rules\AbstarctRule as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboWordCount;
use JBZoo\PHPUnit\Rules\AbstractCellRuleComboTest;

use function JBZoo\PHPUnit\isSame;
use function JBZoo\PHPUnit\success;

class ComboWordCountTest extends AbstractCellRuleComboTest
{
    protected string $ruleClass = ComboWordCount::class;

    public function testEqual(): void
    {
        $rule = $this->create(0, Combo::EQ);
        isSame(null, $rule->validate(''));
        isSame(
            'The word count of the value "cba" is 1, which is not equal than the expected "0"',
            $rule->test('cba'),
        );

        $rule = $this->create(2, Combo::EQ);
        isSame('', $rule->test('asd, asdasd'));
        isSame(
            'The word count of the value "cba" is 1, which is not equal than the expected "2"',
            $rule->test('cba'),
        );
        isSame(
            'The word count of the value "cba 123, 123123" is 1, which is not equal than the expected "2"',
            $rule->test('cba 123, 123123'),
        );

        isSame(
            'The word count of the value "a b c" is 3, which is not equal than the expected "2"',
            $rule->test('a b c'),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(1, Combo::NOT);
        isSame(null, $rule->validate(''));
        isSame(
            'The word count of the value "cba" is 1, which is equal than the not expected "1"',
            $rule->test('cba'),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(0, Combo::MIN);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('cba'));

        $rule = $this->create(2, Combo::MIN);
        isSame('', $rule->test('asd, asdasd'));
        isSame('', $rule->test('asd, asdasd asd'));
        isSame('', $rule->test('asd, asdasd 1232 asdas'));
        isSame(
            'The word count of the value "cba" is 1, which is less than the expected "2"',
            $rule->test('cba'),
        );
        isSame(
            'The word count of the value "cba 123, 123123" is 1, which is less than the expected "2"',
            $rule->test('cba 123, 123123'),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(0, Combo::MAX);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate(''));

        $rule = $this->create(2, Combo::MAX);
        isSame('', $rule->test('asd, asdasd'));
        isSame('', $rule->test('asd, 1232'));
        isSame('', $rule->test('asd, 1232 113234324 342 . ..'));
        isSame(
            'The word count of the value "asd, asdasd asd 1232 asdas" is 4, which is greater than the expected "2"',
            $rule->test('asd, asdasd asd 1232 asdas'),
        );
    }

    public function testInvalidOption(): void
    {
        $this->expectException(\JBZoo\CsvBlueprint\Rules\Exception::class);
        $this->expectExceptionMessage('Invalid option "qwerty" for the "word_count_max" rule. It should be integer.');

        $rule = $this->create('qwerty', Combo::MAX);
        $rule->test('12345');
    }

    public function testInvalidParsing(): void
    {
        success('No cases for invalid parsing.');
    }
}
