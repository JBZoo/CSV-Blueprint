<?php

declare(strict_types=1);

/**
 * Item8 | JBZoo - Csv-Blueprint.
 *
 * This file is part of the Unilead Service Package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license     Proprietary
 * @copyright   Copyright (C) Unilead Network,  All rights reserved.
 * @see         https://www.unileadnetwork.com
 */

namespace JBZoo\PHPUnit\Rules\Cell;

use JBZoo\CsvBlueprint\Rules\AbstractCombo as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboLength;

use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;
use function JBZoo\PHPUnit\skip;

class ComboLengthTest extends AbstractComboTest
{
    protected string $ruleClass = ComboLength::class;

    public function testGetHelp(): void
    {
        $rule = $this->create(6);

        isFileContains($rule->getHelpCombo(), PROJECT_ROOT . '/schema-examples/full.yml');
    }

    public function testEqual(): void
    {
        $rule = $this->create(6);

        isSame('', $rule->test('', Combo::EQ));
        isSame('', $rule->test('123456', Combo::EQ));
        isSame(
            'The length of the value "12345" is 5, which is not equal than the expected "6"',
            $rule->test('12345', Combo::EQ),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(6);

        isSame('', $rule->test('', Combo::MIN));
        isSame('', $rule->test('123456', Combo::MIN));
        isSame('', $rule->test('1234567', Combo::MIN));
        isSame(
            'The length of the value "12345" is 5, which is less than the expected "6"',
            $rule->test('12345', Combo::MIN),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(6);

        isSame('', $rule->test('', Combo::MAX));
        isSame('', $rule->test('123456', Combo::MAX));
        isSame('', $rule->test('12345', Combo::MAX));
        isSame(
            'The length of the value "1234567" is 7, which is greater than the expected "6"',
            $rule->test('1234567', Combo::MAX),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(6);

        isSame('', $rule->test('', Combo::NOT));
        isSame('', $rule->test('12345', Combo::NOT));
        isSame(
            'The length of the value "123456" is 6, which is equal than the expected "6"',
            $rule->test('123456', Combo::NOT),
        );
    }

    public function testInvalidOption(): void
    {
        $this->expectException(\JBZoo\CsvBlueprint\Rules\Exception::class);
        $this->expectExceptionMessage('Invalid option "qwerty" for the "length" rule. It should be integer.');

        $rule = $this->create('qwerty');
        $rule->test('12345', Combo::EQ);
    }

    public function testInvalidParsing(): void
    {
        skip('No cases for invalid parsing.');
    }
}
