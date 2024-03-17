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

use JBZoo\CsvBlueprint\Rules\Cell\ExactValue;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class ExactValueTest extends AbstractCellRule
{
    protected string $ruleClass = ExactValue::class;

    public function testPositive(): void
    {
        $rule = $this->create('123');
        isSame('', $rule->test('123'));
    }

    public function testNegative(): void
    {
        $rule = $this->create('123');
        isSame(
            'Value "" is not strict equal to "123"',
            $rule->test(''),
        );
        isSame(
            'Value "2000-01-02" is not strict equal to "123"',
            $rule->test('2000-01-02'),
        );
    }
}
