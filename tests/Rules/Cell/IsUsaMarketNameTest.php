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

use JBZoo\CsvBlueprint\Rules\Cell\IsUsaMarketName;
use JBZoo\PHPUnit\Rules\AbstractCellRuleTest;

use function JBZoo\PHPUnit\isSame;

final class IsUsaMarketNameTest extends AbstractCellRuleTest
{
    protected string $ruleClass = IsUsaMarketName::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('New York, NY'));
        isSame('', $rule->test('City, ST'));
        isSame('', $rule->test('City-area, ST'));
        isSame('', $rule->test('City.area, ST'));
        isSame('', $rule->test('City.area,asdsa, ST'));

        $rule = $this->create(false);
        isSame(null, $rule->validate(', ST'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        isSame(
            'Invalid market name format for value ", ST". ' .
            'Market name must have format "New York, NY"',
            $rule->test(', ST'),
        );
        isSame(
            'Invalid market name format for value "New York, ny". Market name must have format "New York, NY"',
            $rule->test('New York, ny'),
        );
    }
}
