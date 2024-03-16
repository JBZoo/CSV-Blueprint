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
use JBZoo\CsvBlueprint\Rules\Cell\ComboDate;

use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;

class ComboDateTest extends AbstractComboTest
{
    protected string $ruleClass = ComboDate::class;

    public function testGetHelp(): void
    {
        $rule = $this->create(6);

        isFileContains($rule->getHelpCombo(), PROJECT_ROOT . '/schema-examples/full.yml');
    }

    public function testEqual(): void
    {
        $rule = $this->create('2000-10-02');

        isSame('', $rule->test('', Combo::EQ));
        isSame('', $rule->test('2000-10-02', Combo::EQ));
        isSame('', $rule->test('2000-10-02 00:00:00', Combo::EQ));

        isSame(
            'The date of the value "2000-10-02 00:00:01" is parsed as "2000-10-02 00:00:01 +04:00", ' .
            'which is not equal than the expected "2000-10-02 00:00:00 +04:00 (2000-10-02)"',
            $rule->test('2000-10-02 00:00:01', Combo::EQ),
        );

        isSame(
            'The date of the value "<c>2000-10-02 00:00:01</c>" is parsed as "2000-10-02 00:00:01 +04:00", ' .
            'which is not equal than the expected "<green>2000-10-02 00:00:00 +04:00 (2000-10-02)</green>"',
            $rule->test('2000-10-02 00:00:01', Combo::EQ, true),
        );
    }

    public function testMin(): void
    {
        $rule = $this->create(6);

        isSame('', $rule->test('123456', Combo::MIN));
        isSame('', $rule->test('1234567', Combo::MIN));
        isSame(
            'The length of the "12345" is 5, which is less than the expected "6"',
            $rule->test('12345', Combo::MIN),
        );
    }

    public function testMax(): void
    {
        $rule = $this->create(6);

        isSame('', $rule->test('123456', Combo::MAX));
        isSame('', $rule->test('12345', Combo::MAX));
        isSame(
            'The length of the "1234567" is 7, which is greater than the expected "6"',
            $rule->test('1234567', Combo::MAX),
        );
    }

    public function testNotEqual(): void
    {
        $rule = $this->create(6);

        isSame('', $rule->test('12345', Combo::NOT));
        isSame(
            'The length of the "123456" is 6, which is equal than the not expected "6"',
            $rule->test('123456', Combo::NOT),
        );
    }

    public function testInvalidOption(): void
    {
    }

    public function testInvalidParsing(): void
    {
    }
}
