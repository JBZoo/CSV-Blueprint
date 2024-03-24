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

use JBZoo\CsvBlueprint\Rules\Cell\IsDomain;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsDomainTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsDomain::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('example.com'));
        isSame('', $rule->test('sub.example.com'));
        isSame('', $rule->test('sub.sub.example.com'));
        isSame('', $rule->test('sub.sub-example.com'));
        isSame('', $rule->test('sub-sub-example.com'));
        isSame('', $rule->test('sub-sub-example.qwerty'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('example'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "example" is not a valid domain',
            $rule->test('example'),
        );
    }
}
