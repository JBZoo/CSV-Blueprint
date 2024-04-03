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

use JBZoo\CsvBlueprint\Rules\Cell\IsImei;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsImeiTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsImei::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            '35-209900-176148-1',
            '490154203237518',
            '35-007752-323751-3',
            '35-209900-176148-1',
            '350077523237513',
            '356938035643809',
            '490154203237518',
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), "\"{$value}\"");
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = [
            ';',
            '!@#$%^&*()',
            'aBc 123',
            'aBc-123',
            '490154203237512',
            '4901542032375125',
        ];

        foreach ($invalid as $value) {
            isSame(
                "Value \"{$value}\" is not a valid IMEI number.",
                $rule->test($value),
                $value,
            );
        }
    }
}
