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

use JBZoo\CsvBlueprint\Rules\Cell\CreditCard;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class CreditCardTest extends TestAbstractCellRule
{
    protected string $ruleClass = CreditCard::class;

    public function testPositive(): void
    {
        $rule = $this->create('Any');
        isSame('', $rule->test(''));
        isSame('', $rule->test('5376-7473-9720-8720'));
        isSame('', $rule->test('5376747397208720'));
    }

    public function testNegative(): void
    {
        $rule = $this->create('Any');
        isSame(
            'The value "qwerty" has invalid credit card format for brand "Any".',
            $rule->test('qwerty'),
        );

        $rule = $this->create('Qwerty');
        isSame(
            '"credit_card" at line <red>1</red>, column "prop". '
            . 'Unexpected error: "Qwerty" is not a valid credit card brand '
            . '(Available: Any, American Express, Diners Club, Discover, JCB, MasterCard, Visa, RuPay).',
            (string)$rule->validate('qwerty'),
        );
    }
}
