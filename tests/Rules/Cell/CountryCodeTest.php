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

use JBZoo\CsvBlueprint\Rules\Cell\CountryCode;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;
use Respect\Validation\Rules\CountryCode as RespectCountryCode;

use function JBZoo\PHPUnit\isSame;

final class CountryCodeTest extends TestAbstractCellRule
{
    protected string $ruleClass = CountryCode::class;

    public function testPositive(): void
    {
        $rule = $this->create(RespectCountryCode::ALPHA2);
        isSame('', $rule->test(''));
        isSame('', $rule->test('US'));

        $rule = $this->create(RespectCountryCode::ALPHA3);
        isSame('', $rule->test('USA'));

        $rule = $this->create(RespectCountryCode::NUMERIC);
        isSame('', $rule->test('840'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(RespectCountryCode::ALPHA2);
        isSame(
            'Value "qq" is not a valid "alpha-2" country code.',
            $rule->test('qq'),
        );

        $rule = $this->create(RespectCountryCode::ALPHA3);
        isSame(
            'Value "QQQ" is not a valid "alpha-3" country code.',
            $rule->test('QQQ'),
        );

        $rule = $this->create(RespectCountryCode::NUMERIC);
        isSame(
            'Value "101010101" is not a valid "numeric" country code.',
            $rule->test('101010101'),
        );
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create('qwerty');
        isSame(
            'Unknown country set: "qwerty". Available options: ["alpha-2", "alpha-3", "numeric"]',
            $rule->test('US'),
        );
    }
}
