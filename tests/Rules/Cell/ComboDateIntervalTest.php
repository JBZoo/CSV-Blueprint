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

use JBZoo\CsvBlueprint\Rules\AbstractRule as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboDateInterval;
use JBZoo\PHPUnit\Rules\TestAbstractCellRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboDateIntervalTest extends TestAbstractCellRuleCombo
{
    protected string $ruleClass = ComboDateInterval::class;

    public function testEqual(): void
    {
        $rule = $this->create('1 day', Combo::EQ);

        isSame('', $rule->test(''));
        isSame('', $rule->test('1 day'));

        $rule = $this->create('1 day', Combo::EQ);
        isSame('', $rule->test('1 day'));

        foreach ($rule->getHelpMeta()[1] as $examples) {
            $rule = $this->create($examples[0], Combo::EQ);
            isSame('', $rule->test($examples[0]), $examples[0]);
        }

        isSame(
            'The date interval of the value "<c>qwerty</c>" is '
            . '<red>Can\'t parse date interval: qwerty</red>, '
            . 'which is not equal than the expected "<green>31557600 (P1Y) seconds</green>"',
            $rule->test('qwerty', true),
        );

        $rule = $this->create('P2W', Combo::EQ);
        isSame(
            'The date interval of the value "1 day" is parsed as "86400" seconds, '
            . 'which is not equal than the expected "1209600 (P2W) seconds"',
            $rule->test('1 day'),
        );
    }
}
