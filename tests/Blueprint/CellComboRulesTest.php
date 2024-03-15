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

namespace JBZoo\PHPUnit\Blueprint;

use JBZoo\CsvBlueprint\Rules\AbstractCombo as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboLength;
use JBZoo\PHPUnit\PHPUnit;

use function JBZoo\PHPUnit\isSame;

final class CellComboRulesTest extends PHPUnit
{
    public function testComboLogic(): void
    {
        $rule = new ComboLength('prop', 6);

        // Equal
        isSame('', \strip_tags((string)$rule->validateRuleCombo('123456', Combo::EQ)));
        isSame(
            'The length of the "12345" is 5, which is not equal than the expected "6"',
            \strip_tags((string)$rule->validateRuleCombo('12345', Combo::EQ)),
        );

        // Not equal
        isSame('', \strip_tags((string)$rule->validateRuleCombo('12345', Combo::NOT)));
        isSame(
            'The length of the "123456" is 6, which is equal than the not expected "6"',
            \strip_tags((string)$rule->validateRuleCombo('123456', Combo::NOT)),
        );

        // Min
        isSame('', \strip_tags((string)$rule->validateRuleCombo('123456', Combo::MIN)));
        isSame('', \strip_tags((string)$rule->validateRuleCombo('1234567', Combo::MIN)));
        isSame(
            'The length of the "12345" is 5, which is less than the expected "6"',
            \strip_tags((string)$rule->validateRuleCombo('12345', Combo::MIN)),
        );

        // Max
        isSame('', \strip_tags((string)$rule->validateRuleCombo('123456', Combo::MAX)));
        isSame('', \strip_tags((string)$rule->validateRuleCombo('12345', Combo::MAX)));
        isSame(
            'The length of the "1234567" is 7, which is greater than the expected "6"',
            \strip_tags((string)$rule->validateRuleCombo('1234567', Combo::MAX)),
        );
    }

    public function testGetHelp(): void
    {
        $rule = new ComboLength('prop', 6);
        isSame([
            '# Checks length of a string including spaces (multibyte safe)',
            'length: 5',
            'length_min: 1',
            'length_max: 10',
            'length_not: 42',
        ], $rule->getHelp());
    }
}
