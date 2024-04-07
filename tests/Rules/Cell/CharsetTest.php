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

use JBZoo\CsvBlueprint\Rules\Cell\Charset;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class CharsetTest extends TestAbstractCellRule
{
    protected string $ruleClass = Charset::class;

    public function testPositive(): void
    {
        $rule = $this->create('UTF-8');
        isSame('', $rule->test(''));
        isSame('', $rule->test('USD'));
        isSame('', $rule->test('EUR'));

        $rule = $this->create('ASCII');
        isSame('', $rule->test(\mb_convert_encoding('strawberry', 'ASCII')));
    }

    public function testNegative(): void
    {
        $rule = $this->create('ASCII');
        isSame(
            'The value "日本国" is not in the charset "ASCII".',
            $rule->test('日本国'),
        );

        $rule = $this->create('');
        isSame(
            'The charset is not specified.',
            $rule->test('日本国'),
        );
    }
}
