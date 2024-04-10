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

use JBZoo\CsvBlueprint\Rules\Cell\LanguageCode;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class LanguageCodeTest extends TestAbstractCellRule
{
    protected string $ruleClass = LanguageCode::class;

    public function testPositive(): void
    {
        $rule = $this->create('alpha-2');
        isSame('', $rule->test(''));
        isSame('', $rule->test('en'));
        isSame('', $rule->test('it'));

        $rule = $this->create('alpha-3');
        isSame('', $rule->test('eng'));
        isSame('', $rule->test('ita'));
    }

    public function testNegative(): void
    {
        $rule = $this->create('alpha-2');
        isSame(
            'Value "qq" is not a valid "alpha-2" language code.',
            $rule->test('qq'),
        );
    }

    public function testInvalidOption(): void
    {
        $rule = $this->create('qwerty');
        isSame(
            'Unknown language set: "qwerty". Available options: ["alpha-2", "alpha-3"]',
            $rule->test('US'),
        );
    }
}
