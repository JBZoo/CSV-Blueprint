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

use JBZoo\CsvBlueprint\Rules\Cell\IsCurrencyCode;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class CurrencyCodeTest extends AbstractCellRule
{
    protected string $ruleClass = IsCurrencyCode::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->test(''));
        isSame('', $rule->test('USD'));
        isSame('', $rule->test('EUR'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('qwerty'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "usd" is not a valid currency code (ISO_4217)',
            $rule->test('usd'),
        );
        isSame(
            'Value "qwerty" is not a valid currency code (ISO_4217)',
            $rule->test('qwerty'),
        );
    }
}
